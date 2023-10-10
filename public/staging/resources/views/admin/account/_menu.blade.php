<div class="row">
    <div class="col-md-12">
        <div class="tabbable">
            <ul class="nav nav-tabs nav-tabs-top page-second-nav">
                <li rel0="AccountController/profile">
                    <a href="{{ action("Admin\AccountController@profile") }}" class="level-1">
                        <i class="fe-user position-left"></i> {{ trans('messages.my_profile') }}
                    </a>
                </li>
                <li rel0="AccountController/contact">
                    <a href="{{ action("Admin\AccountController@contact") }}" class="level-1">
                        <i class="fa fa-building-o position-left"></i> {{ trans('messages.contact_information') }}
                    </a>
                </li>
                <li rel0="AccountController/api">
                    <a href="{{ action("Admin\AccountController@api") }}" class="level-1">
                        <i class="icon-key position-left"></i> {{ trans('messages.api_token') }}
                    </a>
                </li>
                <li rel0="NotificationController">
                    <a href="{{ action("Admin\NotificationController@index") }}" class="level-1">
                        <i class="fa fa-bell-o position-left"></i> {{ trans('messages.notifications') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
