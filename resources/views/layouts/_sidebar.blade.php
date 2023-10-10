
            <!-- ========== Left Sidebar Start ========== -->
            <div class="left-side-menu">
                @php            
            $iconDir = "";
            if (Auth::user()->customer->getColorScheme() == 'white') {
                $iconDir = "dark/";
            }
        @endphp
                <div class="h-100" data-simplebar>

                    <!-- User box -->
                    <div class="user-box text-center">
                        <img src="../assets/images/users/user-6.jpg" alt="user-img" title="Mat Helme"
                            class="rounded-circle avatar-md">
                        <div class="dropdown">
                            <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block"
                                data-bs-toggle="dropdown">{{ Auth::user()->displayName() }}</a>
                            <div class="dropdown-menu user-pro-dropdown">

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-user me-1"></i>
                                    <span>My Account</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-settings me-1"></i>
                                    <span>Settings</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-lock me-1"></i>
                                    <span>Lock Screen</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i class="fe-log-out me-1"></i>
                                    <span>Logout</span>
                                </a>

                            </div>
                        </div>
                        <p class="text-muted">Admin Head</p>
                    </div>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <ul id="side-menu">

                          <!--   <li class="menu-title">Navigation</li> -->
                
                            <!-- <li>
                                <a href="{{url('/')}}" >
                                    <i class="fa fa-home"></i>
                                    <span> Dashboards </span>
                                </a>
                                <div class="collapse" id="sidebarDashboards">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="index.html">Dashboard 1</a>
                                        </li>
                                        <li>
                                            <a href="dashboard-2.html">Dashboard 2</a>
                                        </li>
                                        <li>
                                            <a href="dashboard-3.html">Dashboard 3</a>
                                        </li>
                                        <li>
                                            <a href="dashboard-4.html">Dashboard 4</a>
                                        </li>
                                    </ul>
                                </div>
                            </li> -->

                            <!-- <li class="menu-title mt-2">Apps</li> -->

                           <!--  <li>
                                <a href="{{ action('CampaignController@index') }}">
                                     <i class="fe-send"></i>
                                    <span> {{ trans('messages.campaigns') }} </span>
                                </a>
                            </li> -->

                            <li>
                                <a href="{{ url('automation') }}">
                                     <i class="fe-clock"></i>
                                    <span> {{ trans('messages.Automations') }} </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ url('lists') }}">
                                     <i class="fe-list"></i>
                                    <span> Target List </span>
                                </a>
                            </li>
                             @if (Auth::user()->customer->can("read", new Acelle\Model\SendingServer()))
                            <li class="dropdown language-switch"
                                rel0="SendingServerController"
                                rel1="SendingDomainController"
                                rel2="SenderController"
                                rel3="EmailVerificationServerController"
                                rel4="BlacklistController"
                                rel5="TrackingDomainController">
                                <a data-toggle="dropdown" class="dropdown-toggle d-flex align-items-center">
                                    <i class="fe-send"></i>
                                    <span>{{ trans('messages.sending') }}</span>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    @if (Auth::user()->customer->can("read", new Acelle\Model\SendingServer()))
                                        <li rel0="SendingServerController">
                                            <a href="{{ action('SendingServerController@index') }}" class="d-flex align-items-center">
                                                <i class="acelle-icon mr-3" style="width:19px">
                                            <img src="{{ url('images/icons/'.$iconDir.'SVG/server.svg') }}" />
                                            </i> {{ trans('messages.sending_servers') }}
                                            </a>
                                        </li>
                                        <li rel0="BounceHandlerController">
                                            <a href="{{ action('BounceHandlerController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/bounce.svg') }}" />
                                    </i>{{ trans('messages.bounce_handlers') }}
                                            </a>
                                        </li>
                                        <li rel0="FeedbackLoopHandlerController">
                                            <a href="{{ action('FeedbackLoopHandlerController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/feedback.svg') }}" />
                                    </i>{{ trans('messages.feedback_loop_handlers') }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            @endif
                            <li>
                                <a href="{{url('templates')}}"><i class="fe-check-square"></i><span> Templates </span></a>
                            </li>

                            <li class="unsubscribers_icon">
                                <a href="{{url('unsubscribers/list')}}"><i class="fa fa-hand-pointer-o"></i><span> Unsubscribe List </span></a>
                            </li>
                            <!-- <li class="dropdown language-switch"
                                rel0="TrackingLogController"
                                rel1="OpenLogController"
                                rel2="ClickLogController"
                                rel3="FeedbackLogController"
                                rel4="BlacklistController"
                                rel5="UnsubscribeLogController"
                                rel6="BounceLogController">
                                <a data-toggle="dropdown" class="dropdown-toggle d-flex align-items-center">
                                   
<i class="fa fa-file-text-o" aria-hidden="true"></i>
                                    <span>{{ trans('messages.report') }}</span>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                        <li rel0="BlacklistController">
                                            <a href="{{ action('BlacklistController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/blacklist.svg') }}" />
                                    </i>{{ trans('messages.blacklist') }}
                                            </a>
                                        </li>
                                        <li rel0="TrackingLogController">
                                            <a href="{{ action('TrackingLogController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/sreport.svg') }}" />
                                    </i>{{ trans('messages.tracking_log') }}
                                            </a>
                                        </li>
                                        <li rel0="BounceLogController">
                                            <a href="{{ action('BounceLogController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/bounce.svg') }}" />
                                    </i>{{ trans('messages.bounce_log') }}
                                            </a>
                                        </li>
                                        <li rel0="FeedbackLogController">
                                            <a href="{{ action('FeedbackLogController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/feedback.svg') }}" />
                                    </i>Marked as Spam
                                            </a>
                                        </li>
                                        <li rel0="OpenLogController">
                                            <a href="{{ action('OpenLogController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/open.svg') }}" />
                                    </i>{{ trans('messages.open_log') }}
                                            </a>
                                        </li>
                                        <li rel0="ClickLogController">
                                            <a href="{{ action('ClickLogController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/click.svg') }}" />
                                    </i>{{ trans('messages.click_log') }}
                                            </a>
                                        </li>
                                        <li rel0="UnsubscribeLogController">
                                            <a href="{{ action('UnsubscribeLogController@index') }}" class="d-flex align-items-center">
                                            <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/'.$iconDir.'SVG/unsubscribe.svg') }}" />
                                    </i>{{ trans('messages.unsubscribe_log') }}
                                            </a>
                                        </li>
                                </ul>
                            </li> -->

                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
            <!-- Left Sidebar End -->