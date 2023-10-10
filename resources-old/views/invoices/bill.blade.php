@if (!$bill)
    
@else
    <div class="upcoming-bill-box">
        <div class="header">
            <h4 class="mt-0 font-weight-semibold mb-1">{{ $bill['title'] }}</h4>
            <div class="m-0">{!! $bill['description'] !!}</div>        
        </div>
        <div class="body">
            <div>
                @foreach ($bill['bill'] as $item)
                    <div class="bill_item d-flex">
                        <div class="mr-auto">
                            <div class="font-weight-semibold">{{ $item['title'] }}</div>
                            {!! $item['description'] !!}
                        </div>
                        <div class="font-weight-semibold">{{ $item['price'] }}</div>
                    </div>
                    <div class="bill_item d-flex">
                        <div class="mr-auto">
                            <div class="font-weight-semibold">{{ trans('messages.bill.tax') }}</div>
                        </div>
                        <div class="font-weight-semibold">{{ $item['tax'] }}</div>
                    </div>
                @endforeach
            </div>
            <hr>
            <div>
                <div class="total d-flex ">
                    <div class="mr-auto">
                        <div class="font-weight-semibold">{{ trans('messages.bill.estimated_total') }}</div>
                        <div>{!! $bill['charge_info'] !!}</div>
                    </div>
                    <div class="bill-total">{{ $bill['total'] }}</div>
                </div>
            </div>
            
            @if ($bill['pending'])
                <hr>
                <div class="text-right">
                    <a href="{{ action('AccountSubscriptionController@payment') }}" class="btn btn-warning button-loading full-width pr-20" style="pointer-events: auto;opacity:0.9">
                        {{ trans('messages.invoice.payment_is_being_verified') }}
                        <div class="loader"></div>
                    </a>
                </div>
            @elseif (isset($payButton) && !\Auth::user()->customer->preferredPaymentGatewayCanAutoCharge())
                <hr>
                <div class="text-right">
                    <a href="{{ action('AccountSubscriptionController@payment') }}" class="btn btn-mc_primary">
                        {{ trans('messages.invoice.pay_now') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif