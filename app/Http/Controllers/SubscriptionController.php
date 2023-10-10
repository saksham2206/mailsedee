<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Carbon\Carbon;
use Acelle\Library\Facades\SubscriptionFacade;
use Acelle\Model\SubscriptionLog;
/**
 * /api/v1/subscriptions - API controller for managing subscriptions.
 */
class SubscriptionController extends Controller
{
    /**
     * Subscribe customer to a plan (For admin only).
     *
     * POST /api/v1/subscriptions
     *
     * @param \Illuminate\Http\Request $request         All supscription information
     * @param string                   $customer_uid    Customer's uid
     * @param string                   $plan_uid        Plan's uid
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){

         // cronjob check
         SubscriptionFacade::checkExpiration();
         SubscriptionFacade::createRenewInvoice();
         $customers = \Acelle\Model\Customer::all();
         foreach ($customers as $customer) {
             SubscriptionFacade::checkAndAutoPayRenewInvoiceByCustomer($customer);
         }
 
         // init
         $customer = $request->user()->customer;
         $subscription = $customer->getNewOrActiveSubscription();
 
         // 1. HAVE NOT HAD NEW/ACTIVE SUBSCRIPTION YET
         if (!$subscription) {
             // User chưa có subscription sẽ được chuyển qua chọn plan
             return redirect()->action('SubscriptionController@selectPlan');
         }
 
         // 2. IF PLAN NOT ACTIVE
         if (!$subscription->plan->isActive()) {
             return response()->view('errors.general', [ 'message' => __('messages.subscription.error.plan-not-active', [ 'name' => $subscription->plan->name]) ]);
         }
 
         // 3. SUBSCRIPTION IS NEW
         if ($subscription->isNew()) {
             $invoice = $subscription->getItsOnlyUnpaidInitInvoice();
 
             return redirect()->action('SubscriptionController@payment', [
                 'invoice_uid' => $invoice->uid,
             ]);
         }
        return redirect('/');
    }
    public function store(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $customer = \Acelle\Model\Customer::findByUid($request->customer_uid);
        $plan = \Acelle\Model\Plan::findByUid($request->plan_uid);

        // check if customer exists
        if (!is_object($customer)) {
            return \Response::json(array('status' => 0, 'message' => 'Customer not found'), 404);
        }

        // check if plan exists
        if (!is_object($plan)) {
            return \Response::json(array('status' => 0, 'message' => 'Plan not found'), 404);
        }

        // authorize
        if (!$user->can('assignPlan', $customer)) {
            return \Response::json(array('status' => 0, 'message' => 'Unauthorized'), 401);
        }

        // check if item active
        if (!$plan->isActive()) {
            return \Response::json(array('status' => 0, 'message' => 'Plan is not active'), 404);
        }

        $customer->assignPlan($plan);

        return \Response::json(array(
            'status' => 1,
            'message' => 'Assigned '.$customer->user->displayName().' plan to '.$plan->name.' successfully.',
            'customer_uid' => $customer->uid,
            'plan_uid' => $plan->uid
        ), 200);
    }

    public function cancelInvoice(Request $request){

        $customer = $request->user()->customer;
        $subscription = $customer->subscription;

        if ($request->user()->customer->can('cancel', $subscription)) {
            $subscription->cancel();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled'));
        return redirect()->action('AccountSubscriptionController@index');

    }

    //added my himanshu

    public function disableRecurring(Request $request)
    {
        if (isSiteDemo()) {
            return response()->json(["message" => trans('messages.operation_not_allowed_in_demo')], 404);
        }

        $customer = $request->user()->customer;
        $subscription = $customer->getNewOrActiveSubscription();
      
        if ($request->user()->customer->can('disableRecurring', $subscription)) {
            $subscription->disableRecurring();
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.disabled_recurring'));
        return redirect()->action('SubscriptionController@index');
    }

    public function enableRecurring(Request $request)
    {
       
        $customer = $request->user()->customer;
        $subscription = $customer->getNewOrActiveSubscription();

        if ($request->user()->customer->can('enableRecurring', $subscription)) {
            $subscription->enableRecurring();
        }
        // dd("ssss");
        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.enabled_recurring'));
        return redirect()->action('SubscriptionController@index');
    }

    public function payment(Request $request)
    {
        // Get current customer
        $customer = $request->user()->customer;

        // get unpaid invoice
        $invoice = $customer->invoices()->unpaid()->where('uid', '=', $request->invoice_uid)->first();
        
        // no unpaid invoice found
        if (!$invoice) {
            // throw new \Exception('Can not find unpaid invoice with id:' . $request->invoice_uid);
            // just redirect to index
            return redirect()->action('/');
        }

        // nếu đang có pending transaction thì luôn show màn hình pending
        if ($invoice->getPendingTransaction()) {
            return view('subscription.pending', [
                'invoice' => $invoice,
            ]);
        }

        // luôn luôn require billing information
        if (!$invoice->hasBillingInformation()) {
            return redirect()->action('SubscriptionController@billingInformation');
        }

        return view('subscription.payment', [
            'invoice' => $invoice,
        ]);
    }

    public function billingInformation(Request $request)
    {
        $customer = $request->user()->customer;
        $subscription = $customer->getNewOrActiveSubscription();
        $invoice = $subscription->getUnpaidInvoice();
        $billingAddress = $customer->getDefaultBillingAddress();

        // Save posted data
        if ($request->isMethod('post')) {
            $validator = $invoice->updateBillingInformation($request->all());

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('subscription.billingInformation', [
                    'invoice' => $invoice,
                    'billingAddress' => $billingAddress,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Khúc này customer cập nhật thông tin billing information cho lần tiếp theo
            $customer->updateBillingInformationFromInvoice($invoice);

            $request->session()->flash('alert-success', trans('messages.billing_address.updated'));

            // return to subscription
            return redirect()->action('SubscriptionController@payment', [
                'invoice_uid' => $invoice->uid,
            ]);
        }

        // return view('account.billing', [
        //     'invoice' => $invoice,
        //     'billingAddress' => $billingAddress,
        // ]);
        return redirect('/');
    }

    public function checkout(Request $request)
    {
        $customer = $request->user()->customer;
        $invoice = $customer->invoices()->unpaid()->where('uid', '=', $request->invoice_uid)->first();

        // no unpaid invoice found
        if (!$invoice) {
            throw new \Exception('Customer subscription does not have any unpaid invoice!');
        }

        // Luôn đặt payment method mặc định cho customer là lần chọn payment gần nhất
        $request->user()->customer->updatePaymentMethod([
            'method' => $request->payment_method,
        ]);

        // Bỏ qua việc nhập card information khi subscribe plan with trial
        if (\Acelle\Model\Setting::get('not_require_card_for_trial') == 'yes' && $invoice->isInitInvoiceWithTrial()) {
            $invoice->checkout($customer->getPreferredPaymentGateway(), function () {
                return new \Acelle\Cashier\Library\TransactionVerificationResult(\Acelle\Cashier\Library\TransactionVerificationResult::RESULT_DONE);
            });

            return redirect()->action('SubscriptionController@index');
        }

        // redirect to service checkout
        return redirect()->away($customer->getPreferredPaymentGateway()->getCheckoutUrl($invoice));
    }

    //end addded my himanshu
}
