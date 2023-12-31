<div class="row">
    <div class="col-md-12">
        <div class="tabbable">
            <ul class="nav nav-tabs nav-tabs-top page-second-nav">
                <li rel0="AccountController/profile">
                    <a href="{{ action("AccountController@profile") }}" class="level-1">
                        <i class="icon-user position-left"></i> {{ trans('messages.my_profile') }}
                    </a>
                </li>
                <li rel0="AccountController/contact">
                    <a href="{{ action("AccountController@contact") }}" class="level-1">
                        <i class="icon-office position-left"></i> {{ trans('messages.contact_information') }}
                    </a>
                </li>
                <li rel0="AccountController/billing">
                    <a href="{{ action("AccountController@billing") }}" class="level-1">
                        <i class="icon-credit-card position-left"></i> {{ trans('messages.billing') }}
                    </a>
                </li>
                <li rel0="AccountController/subscription"
                    rel1="PaymentController"
                    rel2="AccountController/subscriptionNew"
                    rel3="AccountSubscriptionController"
                    class="{{ isset($tab) && $tab == 'subscription' ? 'active' : '' }}"
                >
                    <a href="{{ action("AccountSubscriptionController@index") }}" class="level-1">
                        <i class="icon-quill4 position-left"></i> {{ trans('messages.subscription') }}
                        @if (Auth::user()->customer->hasSubscriptionNotice())
                            <i class="material-icons-outlined tabs-warning-icon text-danger">info</i>
                        @endif
                    </a>
                </li>
                <li rel0="AccountController/logs">
                    <a href="{{ action("AccountController@logs") }}" class="level-1">
                        <i class="icon-history position-left"></i> {{ trans('messages.logs') }}
                    </a>
                </li>
               
            </ul>
        </div>
    </div>
</div>
