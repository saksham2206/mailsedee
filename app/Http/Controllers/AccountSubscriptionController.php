<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LaravelLog;
use Acelle\Model\Subscription;
use Acelle\Model\Setting;
use Acelle\Model\Plan;
use Acelle\Cashier\Cashier;
use Acelle\Cashier\Services\StripeGatewayService;
use Carbon\Carbon;
use Acelle\Model\SubscriptionLog;

class AccountSubscriptionController extends Controller
{

    
    /**
     * Customer subscription main page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function index(Request $request)
    {
        // 5-12-22 Attar S. Gill
       
        // init
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;

        // 1. HAVE NOT HAD SUBSCRIPTION YET OR SUBSCRIPTION IS ENDED
        if (!$subscription ||
            $subscription->isEnded()
        ) {

            // return Plan::getAvailablePlans();
            return view('account.subscription.select_plan', [
                'plans' => Plan::getAvailablePlans(),
                'subscription' => $subscription,
            ]);
        }

        // @todo không để đây, chỉ test thôi, cần move qua cronjob
        // 1. 1 End luôn subscription nếu đã hết hạn
        //    2 Sinh ra RENEW invoice
        //    3 Xử lý thanh toán
        // $subscription->check();

      

        // 2. IF PLAN NOT ACTIVE
        if (!$subscription->plan->isActive()) {
            return response()->view('errors.general', [ 'message' => __('messages.subscription.error.plan-not-active', [ 'name' => $subscription->plan->name]) ]);
        }
        
        // 3. SUBSCRIPTION IS NEW
        if ($subscription->isNew()) {
            
            $invoice = $subscription->getItsOnlyUnpaidInitInvoice();

            if ($invoice->isNew()) {
                
                

                if (!$invoice->getPendingTransaction()) {
                    if($subscription->plan->price == '0.00'){
                        return redirect("/cashier/offline/checkout/".$subscription->getUnpaidInvoice()->uid);
                    }
                    
                    // site of interest
                    return view('account.subscription.payment', [
                        'subscription' => $subscription,
                        'invoice' => $invoice,
                    ]);
                } else {

                    return view('account.subscription.pending', [
                        'subscription' => $subscription,
                        'invoice' => $invoice,
                    ]);
                }
            } else {
                
                throw new \Exception('There is no such case: new subscription must always have ONE unpaid invoice (which is either "new", and the getItsOnlyUnpaidInitInvoice() method above is supposed to throw an exception before reaching this point.');
            }
        }

        // 3. SUBSCRIPTION IS ACTIVE, SHOW DETAILS PAGE
        return view('account.subscription.index', [
            'subscription' => $subscription,
            'plan' => $subscription->plan,
        ]);
    }

    /**
     * Select plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function init(Request $request)
    {
        // Get current customer
        $customer = $request->user()->customer;
        $plan = Plan::findByUid($request->plan_uid);

        // create new subscription
        $subscription = $customer->assignPlan($plan);
        $option = json_decode($plan->options,true);
        $subscription->email_verification = $option['email_verification_servers_max'];
        $subscription->save();
        // create init invoice
        if (!$subscription->invoices()->new()->count()) {
            $subscription->createInitInvoice();
        }

        // Check if subscriotion is new
        return redirect()->action('AccountSubscriptionController@index');
    }

    /**
     * Checkout payment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function checkout(Request $request)
    {
        // Get current customer
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $gateway = $customer->getPreferredPaymentGateway();
        $invoice = $subscription->getUnpaidInvoice();

        // redirect to service checkout
        return redirect()->away($gateway->getCheckoutUrl($invoice));
    }

    /**
     * Invoice payment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function payment(Request $request)
    {

        // return "test";

        // Get current customer
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $invoice = $subscription->getUnpaidInvoice();


        if ($invoice->isNew()) {
            if ($invoice->getPendingTransaction()) {
                return view('account.subscription.pending', [
                    'subscription' => $subscription,
                    'invoice' => $invoice,
                ]);
            } else {
                // return "tet";
                return view('account.subscription.payment', [
                    'subscription' => $subscription,
                    'invoice' => $invoice,
                ]);
            }
        }

        // invoice was paid
        elseif ($invoice->isPaid()) {
            throw new \Exception('Paid invoice do not need payment screen!');
        }
    }
    
    /**
     * Change plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function changePlan(Request $request)
    {
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $gateway = $customer->getPreferredPaymentGateway();
        $plans = Plan::getAvailablePlans();
        
        // Authorization
        if (!$request->user()->customer->can('changePlan', $subscription)) {
            return $this->notAuthorized();
        }

        //
        if ($request->isMethod('post')) {
            $newPlan = Plan::findByUid($request->plan_uid);

            try {
                // set invoice as pending
                $changePlanInvoice = $subscription->createChangePlanInvoice($newPlan);

                // return $changePlanInvoice;
            } catch (\Exception $e) {
                // return $e->getMessage();
                $request->session()->flash('alert-error', $e->getMessage());
                return redirect()->action('AccountSubscriptionController@index');
            }

            
            // return to subscription
            return redirect()->action('AccountSubscriptionController@payment');
            
        }

        
        return view('account.subscription.change_plan', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plans' => $plans,
        ]);
    }
    
    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;

        if ($request->user()->customer->can('cancel', $subscription)) {
            $subscription->cancel();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled'));
        return redirect()->action('AccountSubscriptionController@index');
    }

    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */

    
    public function customer_cancelInvoice(Request $request)
    {


        $uid = $request->invoice_uid;
        $invoice = \Acelle\Model\Invoice::findByUid($uid);
        $subscription = $request->user()->customer->subscription;

        if (!$request->user()->customer->can('delete', $invoice)) {
            return $this->notAuthorized();
        }

        // if subscription is new -> cancel now subscription.
        // Make sure a new subscription must have a pending invoice
        if ($subscription->isNew()) {
            $subscription->abortNew();
        } else {
            $invoice->delete();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.invoice.cancelled'));
        // return 'Invoice cancelled successfully';

        return redirect('/subscription');
        // return redirect()->action('AccountSubscriptionController@index');
    }

    public function cancelInvoice(Request $request, $uid)
    {

        $invoice = \Acelle\Model\Invoice::findByUid($uid);
        $subscription = $request->user()->customer->subscription;

        if (!$request->user()->customer->can('delete', $invoice)) {
            return $this->notAuthorized();
        }

        // if subscription is new -> cancel now subscription.
        // Make sure a new subscription must have a pending invoice
        if ($subscription->isNew()) {
            $subscription->abortNew();
        } else {
            $invoice->delete();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.invoice.cancelled'));
        return redirect()->action('AccountSubscriptionController@index');
    }

    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function resume(Request $request)
    {
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;

        if ($request->user()->customer->can('resume', $subscription)) {
            $subscription->resume();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.resumed'));
        return redirect()->action('AccountSubscriptionController@index');
    }
    
    /**
     * Cancel now subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelNow(Request $request)
    {
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        
        if ($request->user()->customer->can('cancelNow', $subscription)) {
            $subscription->cancelNow();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled_now'));
        return redirect()->action('AccountSubscriptionController@index');
    }
}
