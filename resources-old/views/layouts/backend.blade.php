<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')

	@include('layouts._css')

    @include('layouts._js')

	@php            
            $iconDir = "";
            if (Auth::user()->customer->getColorScheme() == 'white') {
                $iconDir = "dark/";
            }
        @endphp
	
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

<body class="loading" >

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid">
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
    
          
    
        
            
         <!--    <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <i class="fe-bell noti-icon"></i>
                    <span class="badge bg-danger rounded-circle noti-icon-badge">9</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-lg">
    
                 
                    <div class="dropdown-item noti-title">
                        <h5 class="m-0">
                            <span class="float-end">
                                <a href="" class="text-dark">
                                    <small>Clear All</small>
                                </a>
                            </span>Notification
                        </h5>
                    </div>
    
                    <div class="noti-scroll" data-simplebar>
    
                    
                        <a href="javascript:void(0);" class="dropdown-item notify-item active">
                            <div class="notify-icon">
                                <img src="../assets/images/users/user-1.jpg" class="img-fluid rounded-circle" alt="" /> </div>
                            <p class="notify-details">Cristina Pride</p>
                            <p class="text-muted mb-0 user-msg">
                                <small>Hi, How are you? What about our next meeting</small>
                            </p>
                        </a>
    
                       
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <div class="notify-icon bg-primary">
                                <i class="mdi mdi-comment-account-outline"></i>
                            </div>
                            <p class="notify-details">Caleb Flakelar commented on Admin
                                <small class="text-muted">1 min ago</small>
                            </p>
                        </a>
    
                       
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <div class="notify-icon">
                                <img src="../assets/images/users/user-4.jpg" class="img-fluid rounded-circle" alt="" /> </div>
                            <p class="notify-details">Karen Robinson</p>
                            <p class="text-muted mb-0 user-msg">
                                <small>Wow ! this admin looks good and awesome design</small>
                            </p>
                        </a>
    
                      
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <div class="notify-icon bg-warning">
                                <i class="mdi mdi-account-plus"></i>
                            </div>
                            <p class="notify-details">New user registered.
                                <small class="text-muted">5 hours ago</small>
                            </p>
                        </a>
    
                       
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <div class="notify-icon bg-info">
                                <i class="mdi mdi-comment-account-outline"></i>
                            </div>
                            <p class="notify-details">Caleb Flakelar commented on Admin
                                <small class="text-muted">4 days ago</small>
                            </p>
                        </a>
    
                      
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <div class="notify-icon bg-secondary">
                                <i class="mdi mdi-heart"></i>
                            </div>
                            <p class="notify-details">Carlos Crouch liked
                                <b>Admin</b>
                                <small class="text-muted">13 days ago</small>
                            </p>
                        </a>
                    </div>
    
                 
                    <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">
                        View all
                        <i class="fe-arrow-right"></i>
                    </a>
    
                </div>
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
                      @if (Auth::user()->admin->notifications()->count() == 0)
                        <li class="text-center text-muted2">
                            <span href="#">
                                <i class="lnr lnr-bubble"></i> {{ trans('messages.no_notifications') }}
                            </span>
                        </li>
                    @endif
                    @foreach (Auth::user()->admin->notifications()->take(20)->get() as $notification)
                <li class="media">
                    <div class="media-left">
                        @if ($notification->level == \Acelle\Model\Notification::LEVEL_WARNING)
                            <i class="lnr lnr-warning bg-warning"></i>
                        @elseif ( false &&$notification->level == \Acelle\Model\Notification::LEVEL_ERROR)
                            <i class="lnr lnr-cross bg-danger"></i>
                        @else
                            <i class="lnr lnr-menu bg-info"></i>
                        @endif
                    </div>

                    <div class="media-body">
                        <a href="#" class="media-heading">
                            <span class="text-semibold">{{ $notification->title }}</span>
                            <span class="media-annotation pull-right">{{ $notification->created_at->diffForHumans() }}</span>
                        </a>

                        <span class="text-muted desc text-muted" title='{!! $notification->message !!}'>{{ $notification->message }}</span>
                    </div>
                </li>
            @endforeach
                        
                    </ul>
                    
                   <div class="dropdown-content-footer">
                        <a href="{{ action("Admin\NotificationController@index") }}" data-popup="tooltip" title="{{ trans('messages.all_notifications') }}"><i class="icon-menu display-block"></i></a>
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
                   
    				@can("customer_access", Auth::user())
							<a href="{{ url("/") }}" class="dropdown-item notify-item">
							
							<i class="fe-log-out"></i>{{ trans('messages.customer_view') }}
							</a>
							<div class="dropdown-divider"></div>
						@endif
						<a href="{{ action("Admin\AccountController@profile") }}" class="dropdown-item notify-item">
							<i class="fe-user"></i>
	                        <span>{{ trans('messages.account') }}</span>
						</a>
						
							<!-- <a href="{{ action("Admin\AccountController@api") }}" class="dropdown-item notify-item">
							<i class="fe-lock">
							</i>{{ trans('messages.admin_api') }}
							</a> -->
						
						<a href="{{ url("/logout") }}" class="dropdown-item notify-item">
							<i class="fe-log-out"></i>{{ trans('messages.logout') }}</a>
                    <!-- 
    
                </div>
            </li> -->
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
           <!--  <li>
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
	@include('layouts._sidebar_admin')
    

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
                <div class="content">
                      <div class="container-fluid">
                           @yield('page_header')
                            <div class="sub-section"> 
                               @yield('content')
                           </div>
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
