@extends('layouts.frontend')

@section('title', trans('messages.subscriptions'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li class="active">{{ trans('messages.subscription') }}</li>
        </ul>
    </div>
    
@endsection

@if($subscription->plan->name == 'Free Trial'){

    <a href="https://app.sende.io//cashier/offline/checkout/{{$subscription->getUnpaidInvoice()->uid}}">Submit</a>

}

@endif

@section('content')
    <div class="row">
        <div class="col-md-8">
            @if ($subscription->getUnpaidInvoice() && $subscription->getUnpaidInvoice()->lastTransaction() && $subscription->getUnpaidInvoice()->lastTransaction()->isFailed())
                @include('elements._notification', [
                    'level' => 'error',
                    'message' => $subscription->getUnpaidInvoice()->lastTransaction()->error,
                ])
            @endif
                
            <h2 class="mb-3 mt-0">{{ $invoice->title }}</h2>

            <div class="current_payment">
                @if (request()->user()->customer->getPreferredPaymentGateway() == null)
                    <p>{{ trans('messages.plan.review.do_not_have_payment_yet') }}</p>
                @else
                    <p class="mb-3">{!! trans('messages.payment.you_currenly_payment', [
                        'method' => request()->user()->customer->getPreferredPaymentGateway()->getName(),
                    ]) !!}</p>
                    <form class="" action="{{ action('AccountSubscriptionController@checkout') }}"
                        method="POST">
                        {{ csrf_field() }}

                        <input type="hidden" name="payment_method" value="{{ request()->user()->customer->getPreferredPaymentGateway()->getType() }}" />
                        <input type="hidden" name="return_url" value="{{ action('AccountSubscriptionController@payment') }}" />

                        <input type="submit" name="new_payment"
                            class="btn btn-primary bg-teal-800 py-3 px-4"
                            value="{{ trans('messages.proceed_to_checkout') }}"
                        >

                        <div class="row">
                            <div class="mt-4 col-md-8">{!! trans('messages.payment.agree_service_intro', ['plan' => $subscription->plan->name]) !!}</div>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 other-payment-click">
                        <a href="javascript:;">{{ trans('messages.or_click_choose_another_method') }}</a>
                    </div>
                @endif
            </div>
            
            <form class="edit-payment mt-4 pt-4" {!! (request()->user()->customer->getPreferredPaymentGateway() == null) ? '' : 'style="display:none"' !!} action="{{ request()->user()->customer->getPreferredPaymentGateway() ? action('AccountSubscriptionController@checkout') : action('AccountController@editPaymentMethod') }}"
                method="POST">
                {{ csrf_field() }}

                <p>{{ trans('messages.payment.choose_new_payment_method_to_proceed') }}</p>

                <input type="hidden" name="return_url" value="{{ action('AccountSubscriptionController@payment') }}" />

                <div class="sub-section mb-30 choose-payment-methods">      
                    @foreach(Acelle\Library\Facades\Billing::getEnabledPaymentGateways() as $gateway)
                        <div class="d-flex align-items-center choose-payment choose-payment-{{ $gateway->getType() }}">
                            <div class="text-right pl-2 pr-2">
                                <div class="d-flex align-items-center form-group-mb-0">
                                    @include('helpers.form_control', [
                                        'type' => 'radio2',
                                        'name' => 'payment_method',
                                        'value' => request()->user()->customer->getPreferredPaymentGateway() ? request()->user()->customer->getPreferredPaymentGateway()->getType() : '',
                                        'label' => '',
                                        'help_class' => 'setting',
                                        'rules' => ['payment_method' => 'required'],
                                        'options' => [
                                            ['value' => $gateway->getType(), 'text' => ''],
                                        ],
                                    ])
                                    <div class="check"></div>
                                </div>
                            </div>
                            <div class="mr-auto pr-4">
                                <h4 class="font-weight-semibold mb-2">{{ $gateway->getName() }}</h4>
                                <p class="mb-3">{{ $gateway->getDescription() }}</p>
                            </div>                        
                        </div>
                    @endforeach
                </div>
                
                
                <div class="sub-section">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="submit" name="new_payment"
                                class="btn btn-primary bg-teal-800 py-3 px-4"
                                value="{{ trans('messages.save_payment_method_proceed') }}"
                            >
                        </div>
                        <div class="col-md-8">
                            {!! trans('messages.payment.agree_service_intro', ['plan' => $subscription->plan->name]) !!}
                        </div>
                    </div>
                </div>
            </form>

            @if ($subscription->getUnpaidInvoice()->type !== \Acelle\Model\Invoice::TYPE_RENEW_SUBSCRIPTION)
                <div class="my-4 pt-3">
                    <hr>

                    <a class=""  
                        href="{{ action('AccountSubscriptionController@customer_cancelInvoice', [
                            'invoice_uid' => $subscription->getUnpaidInvoice()->uid,
                        ]) }}">
                        {{ trans('messages.subscription.cancel_now_change_other_plan') }}
                    </a>

                    <!-- Obsolete code <a class=""  link-confirm="{{ trans('messages.invoice.cancel.confirm') }}"
                        href="{{ action('AccountSubscriptionController@customer_cancelInvoice', [
                            'invoice_uid' => $subscription->getUnpaidInvoice()->uid,
                        ]) }}">
                        {{ trans('messages.subscription.cancel_now_change_other_plan') }}
                    </a> -->
                </div>
            @endif
        </div>
        <div class="col-md-4">
            @include('invoices.bill', [
                'bill' => $invoice->getBillingInfo(),
            ])
        </div>
    </div>
    <div class="row">
        
    </div>


    <script>
        $('.edit-payment').on('submit', function(e) {
            if (!$('.choose-payment-methods>div [type=radio]:checked').length) {
                e.preventDefault();

                swal({
                    title: '{{ trans('messages.subscription.no_payment_method_selected') }}',
                    text: "",
                    confirmButtonColor: "#00695C",
                    type: "error",
                    allowOutsideClick: true,
                    confirmButtonText: LANG_OK,
                    customClass: "swl-success",
                    html: true
                });
            }
        });

        $('.choose-payment-methods>div').on('click', function() {
            $(this).find('[type=radio]').prop('checked', true);

            $('.choose-payment-methods>div').removeClass('current');
            $('.choose-payment-methods>div [type=radio]:checked').closest('.choose-payment').addClass('current');

            if ($('.choose-payment-methods>div [type=radio]:checked').val() == '{{ request()->user()->customer->getPreferredPaymentGateway() ? request()->user()->customer->getPreferredPaymentGateway()->getName() : 'none' }}') {
                $('.edit-payment').attr('action', '{!! action('AccountSubscriptionController@checkout') !!}');
            } else {
                $('.edit-payment').attr('action', '{!! action('AccountController@editPaymentMethod') !!}');
            }
        });

        $('.choose-payment-methods>div').removeClass('current');
        $('.choose-payment-methods>div [type=radio]:checked').closest('.choose-payment').addClass('current');        

        if ($('.choose-payment-methods>div [type=radio]').length == 1) {
            $('.choose-payment-methods>div').first().click();
        }

        $('.other-payment-click').on('click', function() {
            $('.edit-payment').show();
            $('.current_payment').hide();
            $(this).hide();
        });
    </script>

@endsection