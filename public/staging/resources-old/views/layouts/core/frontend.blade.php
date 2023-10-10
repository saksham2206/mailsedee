<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')

	@include('layouts._css')
    @include('layouts._js')
	
	
	

</head>
@php            
            $iconDir = "";
            if (Auth::user()->customer->getColorScheme() == 'white') {
                $iconDir = "dark/";
            }
        @endphp
<body class="loading" style="background-color: #f3f7f9;">

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid ">
        <ul class="list-unstyled topnav-menu float-end mb-0">

         
    <!-- 
            <li class="dropdown d-inline-block d-lg-none">
                <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fe-search noti-icon"></i>
                </a>
                <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                    <form class="p-3">
                        <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                    </form>
                </div>
            </li> -->
    
          <!--   <li class="dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-toggle="fullscreen" href="#">
                    <i class="fe-maximize noti-icon"></i>
                </a>
            </li> -->
    
          
    
            <li class="dropdown" style="margin-top: 26px;">
                <a href="#" class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown" style="padding-right:15px;">
                    <i class="acelle-icon"><img src="{{ url('images/icons/'.$iconDir.'SVG/history.svg') }}" /></i>
                    <span class="visible-xs-inline-block position-right">{{ trans('messages.activity_log') }}</span>
                </a>
                
                <div class="dropdown-menu dropdown-content width-350">
                    <div class="dropdown-content-heading">
                        {{ trans('messages.activity_log') }}                        
                    </div>

                    <ul class="media-list dropdown-content-body top-history">
                        @if (Auth::user()->customer->logs()->count() == 0)
                            <li class="text-center text-muted2">
                                <span href="#">
                                    <i class="icon-history"></i> {{ trans('messages.no_activity_logs') }}
                                </span>
                            </li>
                        @endif
                        @foreach (Auth::user()->customer->logs()->take(20)->get() as $log)
                            <li class="media">
                                <div class="media-left">
                                    <img src="{{ $log->customer->user->getProfileImageUrl() }}" class="img-circle img-sm" alt="">
                                </div>

                                <div class="media-body">
                                    <a href="#" class="media-heading">
                                        <span class="text-semibold">{{ $log->customer->user->displayName() }}</span>
                                        <span class="media-annotation pull-right">{{ $log->created_at->diffForHumans() }}</span>
                                    </a>

                                    <span class="text-muted desc text-muted" title='{!! $log->message() !!}'>{!! $log->message() !!}</span>
                                </div>
                            </li>
                        @endforeach
                        
                    </ul>
                    
                    <div class="dropdown-content-footer">
                        <a href="{{ action("AccountController@logs") }}" data-popup="tooltip" title="{{ trans('messages.all_logs') }}"><i class="icon-menu display-block"></i></a>
                    </div>
                </div>
            </li>
            
            
    
            <li class="dropdown  topbar-dropdown">
                <div class="dropdown">
                  <a class=" nav-link dropdown-toggle nav-user me-0 btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="{{ URL::asset('assetsnew/images/users/user-6.jpg') }}"  alt="user-image" class="rounded-circle">
                    <span class="pro-user-name ms-1">
                        {{ Auth::user()->displayName() }} <i class="mdi mdi-chevron-down"></i> 
                    </span>
                  </a>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    @can("admin_access", Auth::user())
                        <a href="{{ url("Admin") }}" class="dropdown-item notify-item">
                            <i class="fe-log-out"></i>
                            {{ trans('messages.admin_view') }}</a>
                              <div class="dropdown-divider"></div>
                        @endif
                            @if (request()->user()->customer->activeSubscription())
                                
                                <a href="#" class="dropdown-item notify-item" data-url="{{ action("AccountController@quotaLog") }}">
                                <i class="fe-user"></i>
                                    <span class="">{{ trans('messages.used_quota') }}</span>
                                </a>
                        
                        @endif
                    <!-- item-->
                    <a href="{{ action('AccountSubscriptionController@index') }}" class="dropdown-item notify-item">
                        <i class="fe-user"></i>
                        <span>{{ trans('messages.subscriptions') }}</span>
                        @if (Auth::user()->customer->hasSubscriptionNotice())
                                    <i class="material-icons-outlined subscription-warning-icon text-danger">info</i>
                                @endif
                    </a>
                      <!-- item-->
                    <a href="{{ action("AccountController@billing") }}" class="dropdown-item notify-item">
                        <i class="fe-settings"></i>
                        <span>{{ trans('messages.billing') }}</span>
                    </a>
    
                    <!-- item-->
                    <a href="{{ action("AccountController@profile") }}" class="dropdown-item notify-item">
                        <i class="fe-settings"></i>
                        <span>{{ trans('messages.account') }}</span>
                    </a>
    
                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock"></i>
                        <span>Lock Screen</span>
                    </a>
                        @if (Auth::user()->customer->canUseApi())
                                <a href="{{ action("AccountController@api") }}" class="dropdown-item notify-item">
                                <i class="fe-lock">
                            </i>{{ trans('messages.campaign_api') }}
                                </a>
                        
                        @endif
                    <div class="dropdown-divider"></div>
    
                    <!-- item-->
                    <a href="{{ url('/logout') }}" class="dropdown-item notify-item">
                        <i class="fe-log-out"></i>
                        <span>Logout</span>
                    </a>
                  </div>
                </div>
                <!-- <a class="nav-link dropdown-toggle nav-user me-0 " data-bs-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                    
                </a>
                <div class="dropdown-menu dropdown-menu-end profile-dropdown "> -->
                   
    				
            </li>
    
         <!--    <li class="dropdown notification-list">
                <a href="javascript:void(0);" class="nav-link right-bar-toggle waves-effect waves-light">
                    <i class="fe-settings noti-icon"></i>
                </a>
            </li> -->
    
        </ul>
    
        <!-- LOGO -->
        <div class="logo-box">
            <a href="{{ url('/') }}" class="logo logo-dark text-center">

            <!--     @if (\Acelle\Model\Setting::get('site_logo_small'))
         
                	<span class="logo-sm">
                    <img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="" height="22">
                   
                </span>
             
                @else

                     <span class="logo-lg">
                    <img src="{{ URL::asset('images/logo-' . (Auth::user()->customer->getColorScheme() == "white" ? "dark" : "light") . '.svg') }}" alt="">
                  
                </span>
                 
                @endif
            </a> -->
    
            <a href="{{url('/')}}" class="logo logo-light text-center">
                <span class="logo-sm">
                    <img src="https://seed.stagingwebsite.space/wp-content/uploads/2021/10/footer.png" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="https://seed.stagingwebsite.space/wp-content/uploads/2021/10/footer.png" alt="" >
                </span>
            </a>
        </div>
    
        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <!-- <li>
                <button class="button-menu-mobile waves-effect waves-light">
                    <i class="fe-menu"></i>
                </button>
            </li> -->

            <li>
                <!-- Mobile menu toggle (Horizontal Layout)-->
                <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>   
            
           
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- end Topbar -->
	@include('layouts._sidebar')
    <script src="{{ URL::asset('assetsnew/js/app.min.js') }}"></script>

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
                <div class="content">
                      <div class="container-fluid ">
                           @yield('page_header')
                         @yield('content')
                      </div>
                </div> 
             </div>   


            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->


        </div>
        <!-- END wrapper -->

        <!-- Right Sidebar -->
        <div class="right-bar">
            
        </div>
        <!-- /Right-bar -->
         <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        @include('layouts._modals');
        <script type="text/javascript">
              $(window).on('load', function () {
                $('.loading').removeClass();
              }) 

        </script>
    </body>
</html>
