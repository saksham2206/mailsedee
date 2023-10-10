<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')

	@include('layouts._css')

	@include('layouts._js')
	
	<!-- Custom langue -->
	<script>
		var LANG_CODE = 'en-US';
	</script>
	@if (Auth::user()->customer->getLanguageCodeFull())
		<script type="text/javascript" src="{{ URL::asset('assets/datepicker/i18n/datepicker.' . Auth::user()->customer->getLanguageCodeFull() . '.js') }}"></script>
		<script>
			LANG_CODE = '{{ Auth::user()->customer->getLanguageCodeFull() }}';
		</script>
	@endif

	<script>
		$.cookie('last_language_code', '{{ Auth::user()->customer->getLanguageCode() }}');
	</script>

</head>

<body class="navbar-top color-scheme-{{ Auth::user()->customer->getColorScheme() }}">

	<header class="automation-header">
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
			 <a href="{{url('/')}}" class="logo logo-light text-center">
			 	@if (\Acelle\Model\Setting::get('site_logo_small'))
                <span class="logo-sm left-logo">
                    <img src="{{ URL::asset('assets/images/logo-black.png') }}" alt="" style="width: 147px;">
                </span>
                 <span class="logo-lg left-logo">
                    <img src="{{ URL::asset('assets/images/logo-black.png') }}" alt="" style="width: 147px;">
                </span>
                @else
                 <span class="logo-sm">
                    <img src="{{ URL::asset('assets/images/logo-black.png') }}" alt="" style="width: 147px;">
                </span>
                 <span class="logo-lg">
                    <img src="{{ URL::asset('assets/images/logo-black.png') }}" alt="" style="width: 147px;" >
                </span>
                @endif
            </a>
			<!-- <a class="navbar-brand left-logo" href="#" style="padding: 7px 20px !important ;">
				@if (\Acelle\Model\Setting::get('site_logo_small'))
					<img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
				@else
					<img  src="{{ URL::asset('images/logo_light_blue.svg') }}" alt="">
				@endif
			</a> -->
			<div class="d-inline-block d-flex mr-auto align-items-center">
				<h1 class="">{{ $automation->name }}</h1>
				<i class="material-icons-outlined automation-head-icon ml-2">alarm</i>
			</div>
			<div class="automation-top-menu">
				{{-- <span class="mr-3 last_save_time"><i>{{ trans('messages.automation.designer.last_saved', ['time' => $automation->updated_at->diffForHumans()]) }}</i></span> --}}
				<a href="{{ action('Automation2Controller@index') }}" class="action">
					<i class="material-icons-outlined mr-2">arrow_back</i>
					{{ trans('messages.automation.go_back') }}
				</a>

				<div class="switch-automation d-flex">
					<select class="select select2 top-menu-select" name="switch_automation">
						@foreach($automation->getSwitchAutomations(Auth::user()->customer)->get() as $auto)
							<option value='{{ action('Automation2Controller@subscribers', $auto->uid) }}'>{{ $auto->name }}</option>
						@endforeach
					</select>

					<a href="javascript:'" class="action">
						<i class="material-icons-outlined mr-2">
						horizontal_split
						</i>
						{{ trans('messages.automation.switch_automation') }}
					</a>
				</div>

<div class="account-info">
                    <ul class="navbar-nav mr-auto navbar-dark bg-dark">                        
                        <li class="nav-item dropdown">
                            <a class="account-item nav-link dropdown-toggle px-2 pro-user-name ms-1" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="avatar" src="{{ Auth::user()->getProfileImageUrl() }}" alt="">
                                {{ Auth::user()->displayName() }}
                                <i class="mdi mdi-chevron-down"></i>
                            </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    @can("admin_access", Auth::user())
                        <a href="{{ action("Admin\HomeController@index") }}" class="dropdown-item notify-item">
                            <i class="fe-user"></i>
                            {{ trans('messages.admin_view') }}</a>
                              <!-- <div class="dropdown-divider"></div> -->
                        @endif
                       <!--  @if (request()->user()->customer->activeSubscription())
                                
                                <a href="#" class="dropdown-item notify-item" data-url="{{ action("AccountController@quotaLog") }}">
                                <i class="fe-user"></i>
                                    <span class="">{{ trans('messages.used_quota') }}</span>
                                </a>
                        
                        @endif -->
                    <!-- item-->
                    <a href="{{ action('AccountSubscriptionController@index') }}" class="dropdown-item notify-item">
                        <i class="fa fa-hand-pointer-o" aria-hidden="true"></i>
                        <span>{{ trans('messages.subscriptions') }}</span>
                        @if (Auth::user()->customer->hasSubscriptionNotice())
                                    <i class="material-icons-outlined subscription-warning-icon text-danger">info</i>
                                @endif
                    </a>
                      <!-- item-->
                    <a href="{{ action("AccountController@billing") }}" class="dropdown-item notify-item">
                       <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        <span>{{ trans('messages.billing') }}</span>
                    </a>
    
                    <!-- item-->
                    <a href="{{ action("AccountController@profile") }}" class="dropdown-item notify-item">
                        <i class="fe-settings"></i>
                        <span>{{ trans('messages.account') }}</span>
                    </a>
    
                    <!-- item-->
                  <!--   <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock"></i>
                        <span>Lock Screen</span>
                    </a> -->
                        <!-- @if (Auth::user()->customer->canUseApi())
                                <a href="{{ action("AccountController@api") }}" class="dropdown-item notify-item">
                                <i class="fe-lock"></i>
                                <span>{{ trans('messages.campaign_api') }}</span>
                                </a>
                        
                        @endif -->
                    <!-- <div class="dropdown-divider"></div> -->
    
                    <!-- item-->
                    <a href="{{ url('/logout') }}" class="dropdown-item notify-item">
                        <i class="fe-log-out"></i>
                        <span>Logout</span>
                    </a>
                  </div>
                        </li>
                    </ul>
                    
                </div>
			</div>
			
		</nav>
	</header>

	<!-- Page header -->
	<div class="page-header">
		<div class="page-header-content">

			@yield('page_header')

		</div>
	</div>
	<!-- /page header -->

	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- display flash message -->
				@include('common.errors')

				<!-- main inner content -->
				@yield('content')

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->


		<!-- Footer -->
	<!-- 	<div class="footer text-muted">
			{!! trans('messages.copy_right') !!}
		</div> -->
		<!-- /footer -->

	</div>
	<!-- /page container -->

	@include("layouts._modals")

        {!! \Acelle\Model\Setting::get('custom_script') !!}

</body>
</html>
