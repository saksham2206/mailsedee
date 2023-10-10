
            <!-- ========== Left Sidebar Start ========== -->
            <div class="left-side-menu">
                @php            
            $iconDir = "";
            if (Auth::user()->admin->getColorScheme() == 'white') {
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

                          <li rel0="HomeController">
                    <a href="{{ action('Admin\HomeController@index') }}" class="d-flex align-items-center">
                        <i class="fe-home acelle-icon mr-3">
                        </i>
                        {{ trans('messages.dashboard') }}
                    </a>
                </li>

                @if (Auth::user()->can("read", new Acelle\Model\Customer()))
                    <li class="dropdown language-switch"
                        rel0="CustomerGroupController"
                        rel1="CustomerController"
                    >
                        <a class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                            <i class="fe-user acelle-icon mr-3" style="width: 19px;">
                            </i>
                            {{ trans('messages.customer') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            @if (Auth::user()->can("read", new Acelle\Model\Customer()))
                                <li rel0="CustomerController">
                                    <a href="{{ action('Admin\CustomerController@index') }}" class="d-flex align-items-center">
                                        <i class="acelle-icon mr-3">
                                            <img src="{{ url('images/icons/SVG/customers.svg') }}" />
                                        </i>
                                        {{ trans('messages.customers') }}
                                    </a>
                                </li>
                            @endif
                            <li rel0="SubscriptionController">
                                <a href="{{ action('Admin\SubscriptionController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                        <img src="{{ url('images/icons/SVG/subscription.svg') }}" />
                                    </i>
                                    {{ trans('messages.subscriptions') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (
                    Auth::user()->can("read", new Acelle\Model\Plan())
                    || Auth::user()->can("read", new Acelle\Model\Currency())
                )
                    <li class="dropdown language-switch"
                        rel0="PlanController"
                        rel1="CurrencyGroupController"
                    >
                        <a class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                            <i class="icon-clipboard3 acelle-icon mr-3" style="width: 23px;">
                                <!-- <img src="{{ url('images/icons/'.$iconDir.'SVG/plan.svg') }}" /> -->
                            </i>{{ trans('messages.plan') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            @if (Auth::user()->can("read", new Acelle\Model\Plan()))
                                <li rel0="PlanController">
                                    <a href="{{ action('Admin\PlanController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3" style="width: 20px;">
                                        <img src="{{ url('images/icons/SVG/plans.svg') }}" />
                                    </i>{{ trans('messages.plans') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->can("read", new Acelle\Model\Currency()))
                                <li rel0="CurrencyController">
                                    <a href="{{ action('Admin\CurrencyController@index') }}" class="d-flex align-items-center">
                                        <i class="acelle-icon mr-3" style="width: 20px;">
                                            <img src="{{ url('images/icons/SVG/currency.svg') }}" />
                                        </i>{{ trans('messages.currencies') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (
                    Auth::user()->admin->getPermission("admin_read") != 'no'
                    || Auth::user()->admin->getPermission("admin_group_read") != 'no'
                )
                    <li class="dropdown language-switch"
                        rel0="AdminGroupController"
                        rel1="AdminController"
                    >
                        <a class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                        <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/admin.svg') }}" />
                            </i>{{ trans('messages.admin') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            @if (Auth::user()->admin->getPermission("admin_read") != 'no')
                                <li rel0="AdminController">
                                    <a href="{{ action('Admin\AdminController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/admins.svg') }}" />
                            </i>{{ trans('messages.admins') }}                                      
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("admin_group_read") != 'no')
                                <li rel0="AdminGroupController">
                                    <a href="{{ action('Admin\AdminGroupController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/group.svg') }}" />
                            </i>{{ trans('messages.admin_groups') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (
                    Auth::user()->admin->getPermission("sending_domain_read") != 'no'
                    || Auth::user()->admin->getPermission("sending_server_read") != 'no'
                    || Auth::user()->admin->getPermission("bounce_handler_read") != 'no'
                    || Auth::user()->admin->getPermission("fbl_handler_read") != 'no'
                    || Auth::user()->admin->getPermission("email_verification_server_read") != 'no'
                    || Auth::user()->admin->can('read', new \Acelle\Model\SubAccount())
                )
                    <li class="dropdown language-switch"
                        rel0="BounceHandlerController"
                        rel1="FeedbackLoopHandlerController"
                        rel2="SendingServerController"
                        rel3="SendingDomainController"
                        rel4="SubAccountController"
                        rel5="EmailVerificationServerController"
                    >
                        <a class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                        <i class="fe-send acelle-icon mr-3">
                                <!-- <img src="{{ url('images/icons/'.$iconDir.'SVG/sending.svg') }}" /> -->
                            </i> {{ trans('messages.sending') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            @if (Auth::user()->admin->getPermission("sending_server_read") != 'no')
                                <li rel0="SendingServerController">
                                    <a href="{{ action('Admin\SendingServerController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/server.svg') }}" />
                            </i>{{ trans('messages.sending_severs') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->can('read', new \Acelle\Model\SubAccount()))
                                <li rel0="SubAccountController">
                                    <a href="{{ action('Admin\SubAccountController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/account.svg') }}" />
                            </i>{{ trans('messages.sub_accounts') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("bounce_handler_read") != 'no')
                                <li rel0="BounceHandlerController">
                                    <a href="{{ action('Admin\BounceHandlerController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/bounce.svg') }}" />
                            </i>{{ trans('messages.bounce_handlers') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("fbl_handler_read") != 'no')
                                <li rel0="FeedbackLoopHandlerController">
                                    <a href="{{ action('Admin\FeedbackLoopHandlerController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/feedback.svg') }}" />
                            </i>{{ trans('messages.feedback_loop_handlers') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("email_verification_server_read") != 'no')
                                <li rel0="EmailVerificationServerController">
                                    <a href="{{ action('Admin\EmailVerificationServerController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/vserver.svg') }}" />
                            </i>{{ trans('messages.email_verification_servers') }}
                                    </a>
                                </li>
                            @endif                          
                        </ul>
                    </li>
                @endif
                <li class="dropdown language-switch"
                    rel0="TemplateController"
                    rel1="LayoutController"
                    rel2="LanguageController"
                    rel3="SettingController"
                    rel4="PaymentController"
                    rel5="PluginController"
                >
                    <a class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                    <i class="fe-settings acelle-icon mr-3">
                                <!-- <img src="{{ url('images/icons/'.$iconDir.'SVG/setting.svg') }}" /> -->
                            </i>{{ trans('messages.setting') }}
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (
                            Auth::user()->admin->getPermission("setting_general") != 'no' ||
                            Auth::user()->admin->getPermission("setting_sending") != 'no' ||
                            Auth::user()->admin->getPermission("setting_system_urls") != 'no' ||
                            Auth::user()->admin->getPermission("setting_background_job") != 'no'
                        )
                            <li rel0="SettingController">
                                <a href="{{ action('Admin\SettingController@index') }}" class="d-flex align-items-center">
                                <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/setting2.svg') }}" />
                            </i>{{ trans('messages.all_settings') }}
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("template_read") != 'no')
                            <li rel0="TemplateController">
                                <a href="{{ action('Admin\TemplateController@index') }}" class="d-flex align-items-center">
                                <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/dtemplate.svg') }}" />
                            </i>{{ trans('messages.template_gallery') }}
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("layout_read") != 'no')
                            <li rel0="LayoutController">
                                <a href="{{ action('Admin\LayoutController@index') }}" class="d-flex align-items-center">
                                <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/page.svg') }}" />
                            </i>{{ trans('messages.page_form_layout') }}
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("language_read") != 'no')
                            <li rel0="LanguageController">
                                <a href="{{ action('Admin\LanguageController@index') }}" class="d-flex align-items-center">
                                <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/lang.svg') }}" />
                            </i>{{ trans('messages.language') }}
                                </a>
                            </li>
                        @endif
                        @if (Auth::user()->admin->getPermission("payment_method_read") != 'no')
                            <li rel0="PaymentController">
                                <a href="{{ action('Admin\PaymentController@index') }}" class="d-flex align-items-center">
                                <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/card.svg') }}" />
                            </i>{{ trans('messages.payment_gateways') }}
                                </a>
                            </li>
                        @endif
                       <!--  <li rel0="PluginController">
                            <a href="{{ action('Admin\PluginController@index') }}" class="d-flex align-items-center">
                            <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/plugin.svg') }}" />
                            </i>{{ trans('messages.plugins') }}
                            </a>
                        </li> -->
                    </ul>
                </li>

                @if (
                    Auth::user()->admin->getPermission("report_blacklist") != 'no'
                    || Auth::user()->admin->getPermission("report_tracking_log") != 'no'
                    || Auth::user()->admin->getPermission("report_bounce_log") != 'no'
                    || Auth::user()->admin->getPermission("report_feedback_log") != 'no'
                    || Auth::user()->admin->getPermission("report_open_log") != 'no'
                    || Auth::user()->admin->getPermission("report_click_log") != 'no'
                    || Auth::user()->admin->getPermission("report_unsubscribe_log") != 'no'
                )
                    <li class="dropdown language-switch"
                        rel0="TrackingLogController"
                        rel1="OpenLogController"
                        rel2="ClickLogController"
                        rel3="FeedbackLogController"
                        rel4="BlacklistController"
                        rel5="UnsubscribeLogController"
                        rel6="BounceLogController"
                    >
                        <a class="dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                            <i class="fa fa-file-text-o acelle-icon mr-3">
                                <!-- <img src="{{ url('images/icons/'.$iconDir.'SVG/report.svg') }}" /> -->
                            </i>{{ trans('messages.report') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            @if (Auth::user()->admin->getPermission("report_blacklist") != 'no')
                                <li rel0="BlacklistController">
                                    <a href="{{ action('Admin\BlacklistController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/blacklist.svg') }}" />
                            </i>{{ trans('messages.blacklist') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_tracking_log") != 'no')
                                <li rel0="TrackingLogController">
                                    <a href="{{ action('Admin\TrackingLogController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/sreport.svg') }}" />
                            </i>{{ trans('messages.tracking_log') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_bounce_log") != 'no')
                                <li rel0="BounceLogController">
                                    <a href="{{ action('Admin\BounceLogController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/bounce.svg') }}" />
                            </i>{{ trans('messages.bounce_log') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_feedback_log") != 'no')
                                <li rel0="FeedbackLogController">
                                    <a href="{{ action('Admin\FeedbackLogController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/feedback.svg') }}" />
                            </i>{{ trans('messages.feedback_log') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_open_log") != 'no')
                                <li rel0="OpenLogController">
                                    <a href="{{ action('Admin\OpenLogController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/open.svg') }}" />
                            </i>{{ trans('messages.open_log') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_click_log") != 'no')
                                <li rel0="ClickLogController">
                                    <a href="{{ action('Admin\ClickLogController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/click.svg') }}" />
                            </i>{{ trans('messages.click_log') }}
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->admin->getPermission("report_unsubscribe_log") != 'no')
                                <li rel0="UnsubscribeLogController">
                                    <a href="{{ action('Admin\UnsubscribeLogController@index') }}" class="d-flex align-items-center">
                                    <i class="acelle-icon mr-3">
                                <img src="{{ url('images/icons/'.$iconDir.'SVG/unsubscribe.svg') }}" />
                            </i>{{ trans('messages.unsubscribe_log') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
<!-- 
                            <li>
                                <a href="#sidebarEcommerce" data-bs-toggle="collapse">
                                    <i data-feather="shopping-cart"></i>
                                    <span> Ecommerce </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEcommerce">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="ecommerce-dashboard.html">Dashboard</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-products.html">Products</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-product-detail.html">Product Detail</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-product-edit.html">Add Product</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-customers.html">Customers</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-orders.html">Orders</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-order-detail.html">Order Detail</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-sellers.html">Sellers</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-cart.html">Shopping Cart</a>
                                        </li>
                                        <li>
                                            <a href="ecommerce-checkout.html">Checkout</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarCrm" data-bs-toggle="collapse">
                                    <i data-feather="users"></i>
                                    <span> CRM </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarCrm">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="crm-dashboard.html">Dashboard</a>
                                        </li>
                                        <li>
                                            <a href="crm-contacts.html">Contacts</a>
                                        </li>
                                        <li>
                                            <a href="crm-opportunities.html">Opportunities</a>
                                        </li>
                                        <li>
                                            <a href="crm-leads.html">Leads</a>
                                        </li>
                                        <li>
                                            <a href="crm-customers.html">Customers</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarEmail" data-bs-toggle="collapse">
                                    <i data-feather="mail"></i>
                                    <span> Email </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarEmail">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="email-inbox.html">Inbox</a>
                                        </li>
                                        <li>
                                            <a href="email-read.html">Read Email</a>
                                        </li>
                                        <li>
                                            <a href="email-compose.html">Compose Email</a>
                                        </li>
                                        <li>
                                            <a href="email-templates.html">Email Templates</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="apps-social-feed.html">
                                    <span class="badge bg-pink float-end">Hot</span>
                                    <i data-feather="rss"></i>
                                    <span> Social Feed </span>
                                </a>
                            </li>

                            <li>
                                <a href="apps-companies.html">
                                    <i data-feather="activity"></i>
                                    <span> Companies </span>
                                </a>
                            </li>

                            <li>
                                <a href="#sidebarProjects" data-bs-toggle="collapse">
                                    <i data-feather="briefcase"></i>
                                    <span> Projects </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarProjects">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="project-list.html">List</a>
                                        </li>
                                        <li>
                                            <a href="project-detail.html">Detail</a>
                                        </li>
                                        <li>
                                            <a href="project-create.html">Create Project</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarTasks" data-bs-toggle="collapse">
                                    <i data-feather="clipboard"></i>
                                    <span> Tasks </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTasks">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="task-list.html">List</a>
                                        </li>
                                        <li>
                                            <a href="task-details.html">Details</a>
                                        </li>
                                        <li>
                                            <a href="task-kanban-board.html">Kanban Board</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarContacts" data-bs-toggle="collapse">
                                    <i data-feather="book"></i>
                                    <span> Contacts </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarContacts">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="contacts-list.html">Members List</a>
                                        </li>
                                        <li>
                                            <a href="contacts-profile.html">Profile</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarTickets" data-bs-toggle="collapse">
                                    <i data-feather="aperture"></i>
                                    <span> Tickets </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTickets">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="tickets-list.html">List</a>
                                        </li>
                                        <li>
                                            <a href="tickets-detail.html">Detail</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="apps-file-manager.html">
                                    <i data-feather="folder-plus"></i>
                                    <span> File Manager </span>
                                </a>
                            </li>

                            <li class="menu-title mt-2">Custom</li>

                            <li>
                                <a href="#sidebarAuth" data-bs-toggle="collapse">
                                    <i data-feather="file-text"></i>
                                    <span> Auth Pages </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarAuth">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="auth-login.html">Log In</a>
                                        </li>
                                        <li>
                                            <a href="auth-login-2.html">Log In 2</a>
                                        </li>
                                        <li>
                                            <a href="auth-register.html">Register</a>
                                        </li>
                                        <li>
                                            <a href="auth-register-2.html">Register 2</a>
                                        </li>
                                        <li>
                                            <a href="auth-signin-signup.html">Signin - Signup</a>
                                        </li>
                                        <li>
                                            <a href="auth-signin-signup-2.html">Signin - Signup 2</a>
                                        </li>
                                        <li>
                                            <a href="auth-recoverpw.html">Recover Password</a>
                                        </li>
                                        <li>
                                            <a href="auth-recoverpw-2.html">Recover Password 2</a>
                                        </li>
                                        <li>
                                            <a href="auth-lock-screen.html">Lock Screen</a>
                                        </li>
                                        <li>
                                            <a href="auth-lock-screen-2.html">Lock Screen 2</a>
                                        </li>
                                        <li>
                                            <a href="auth-logout.html">Logout</a>
                                        </li>
                                        <li>
                                            <a href="auth-logout-2.html">Logout 2</a>
                                        </li>
                                        <li>
                                            <a href="auth-confirm-mail.html">Confirm Mail</a>
                                        </li>
                                        <li>
                                            <a href="auth-confirm-mail-2.html">Confirm Mail 2</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarExpages" data-bs-toggle="collapse">
                                    <i data-feather="package"></i>
                                    <span> Extra Pages </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarExpages">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="pages-starter.html">Starter</a>
                                        </li>
                                        <li>
                                            <a href="pages-timeline.html">Timeline</a>
                                        </li>
                                        <li>
                                            <a href="pages-sitemap.html">Sitemap</a>
                                        </li>
                                        <li>
                                            <a href="pages-invoice.html">Invoice</a>
                                        </li>
                                        <li>
                                            <a href="pages-faqs.html">FAQs</a>
                                        </li>
                                        <li>
                                            <a href="pages-search-results.html">Search Results</a>
                                        </li>
                                        <li>
                                            <a href="pages-pricing.html">Pricing</a>
                                        </li>
                                        <li>
                                            <a href="pages-maintenance.html">Maintenance</a>
                                        </li>
                                        <li>
                                            <a href="pages-coming-soon.html">Coming Soon</a>
                                        </li>
                                        <li>
                                            <a href="pages-gallery.html">Gallery</a>
                                        </li>
                                        <li>
                                            <a href="pages-404.html">Error 404</a>
                                        </li>
                                        <li>
                                            <a href="pages-404-two.html">Error 404 Two</a>
                                        </li>
                                        <li>
                                            <a href="pages-404-alt.html">Error 404-alt</a>
                                        </li>
                                        <li>
                                            <a href="pages-500.html">Error 500</a>
                                        </li>
                                        <li>
                                            <a href="pages-500-two.html">Error 500 Two</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarLayouts" data-bs-toggle="collapse">
                                    <i data-feather="layout"></i>
                                    <span class="badge bg-blue float-end">New</span>
                                    <span> Layouts </span>
                                </a>
                                <div class="collapse" id="sidebarLayouts">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="layouts-vertical.html">Vertical</a>
                                        </li>
                                        <li>
                                            <a href="layouts-horizontal.html">Horizontal</a>
                                        </li>
                                        <li>
                                            <a href="layouts-two-column.html">Two Column Menu</a>
                                        </li>
                                        <li>
                                            <a href="layouts-two-tone-icons.html">Two Tones Icons</a>
                                        </li>
                                        <li>
                                            <a href="layouts-preloader.html">Preloader</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li class="menu-title mt-2">Components</li>

                            <li>
                                <a href="#sidebarBaseui" data-bs-toggle="collapse">
                                    <i data-feather="pocket"></i>
                                    <span> Base UI </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarBaseui">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="ui-buttons.html">Buttons</a>
                                        </li>
                                        <li>
                                            <a href="ui-cards.html">Cards</a>
                                        </li>
                                        <li>
                                            <a href="ui-avatars.html">Avatars</a>
                                        </li>
                                        <li>
                                            <a href="ui-portlets.html">Portlets</a>
                                        </li>
                                        <li>
                                            <a href="ui-tabs-accordions.html">Tabs & Accordions</a>
                                        </li>
                                        <li>
                                            <a href="ui-modals.html">Modals</a>
                                        </li>
                                        <li>
                                            <a href="ui-progress.html">Progress</a>
                                        </li>
                                        <li>
                                            <a href="ui-notifications.html">Notifications</a>
                                        </li>
                                        <li>
                                            <a href="ui-offcanvas.html">Offcanvas</a>
                                        </li>
                                        <li>
                                            <a href="ui-placeholders.html">Placeholders</a>
                                        </li>
                                        <li>
                                            <a href="ui-spinners.html">Spinners</a>
                                        </li>
                                        <li>
                                            <a href="ui-images.html">Images</a>
                                        </li>
                                        <li>
                                            <a href="ui-carousel.html">Carousel</a>
                                        </li>
                                        <li>
                                            <a href="ui-list-group.html">List Group</a>
                                        </li>
                                        <li>
                                            <a href="ui-video.html">Embed Video</a>
                                        </li>
                                        <li>
                                            <a href="ui-dropdowns.html">Dropdowns</a>
                                        </li>
                                        <li>
                                            <a href="ui-ribbons.html">Ribbons</a>
                                        </li>
                                        <li>
                                            <a href="ui-tooltips-popovers.html">Tooltips & Popovers</a>
                                        </li>
                                        <li>
                                            <a href="ui-general.html">General UI</a>
                                        </li>
                                        <li>
                                            <a href="ui-typography.html">Typography</a>
                                        </li>
                                        <li>
                                            <a href="ui-grid.html">Grid</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarExtendedui" data-bs-toggle="collapse">
                                    <i data-feather="layers"></i>
                                    <span class="badge bg-info float-end">Hot</span>
                                    <span> Extended UI </span>
                                </a>
                                <div class="collapse" id="sidebarExtendedui">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="extended-nestable.html">Nestable List</a>
                                        </li>
                                        <li>
                                            <a href="extended-range-slider.html">Range Slider</a>
                                        </li>
                                        <li>
                                            <a href="extended-dragula.html">Dragula</a>
                                        </li>
                                        <li>
                                            <a href="extended-animation.html">Animation</a>
                                        </li>
                                        <li>
                                            <a href="extended-sweet-alert.html">Sweet Alert</a>
                                        </li>
                                        <li>
                                            <a href="extended-tour.html">Tour Page</a>
                                        </li>
                                        <li>
                                            <a href="extended-scrollspy.html">Scrollspy</a>
                                        </li>
                                        <li>
                                            <a href="extended-loading-buttons.html">Loading Buttons</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="widgets.html">
                                    <i data-feather="gift"></i>
                                    <span> Widgets </span>
                                </a>
                            </li>

                            <li>
                                <a href="#sidebarIcons" data-bs-toggle="collapse">
                                    <i data-feather="cpu"></i>
                                    <span> Icons </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarIcons">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="icons-two-tone.html">Two Tone Icons</a>
                                        </li>
                                        <li>
                                            <a href="icons-feather.html">Feather Icons</a>
                                        </li>
                                        <li>
                                            <a href="icons-mdi.html">Material Design Icons</a>
                                        </li>
                                        <li>
                                            <a href="icons-dripicons.html">Dripicons</a>
                                        </li>
                                        <li>
                                            <a href="icons-font-awesome.html">Font Awesome 5</a>
                                        </li>
                                        <li>
                                            <a href="icons-themify.html">Themify</a>
                                        </li>
                                        <li>
                                            <a href="icons-simple-line.html">Simple Line</a>
                                        </li>
                                        <li>
                                            <a href="icons-weather.html">Weather</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarForms" data-bs-toggle="collapse">
                                    <i data-feather="bookmark"></i>
                                    <span> Forms </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarForms">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="forms-elements.html">General Elements</a>
                                        </li>
                                        <li>
                                            <a href="forms-advanced.html">Advanced</a>
                                        </li>
                                        <li>
                                            <a href="forms-validation.html">Validation</a>
                                        </li>
                                        <li>
                                            <a href="forms-pickers.html">Pickers</a>
                                        </li>
                                        <li>
                                            <a href="forms-wizard.html">Wizard</a>
                                        </li>
                                        <li>
                                            <a href="forms-masks.html">Masks</a>
                                        </li>
                                        <li>
                                            <a href="forms-quilljs.html">Quilljs Editor</a>
                                        </li>
                                        <li>
                                            <a href="forms-file-uploads.html">File Uploads</a>
                                        </li>
                                        <li>
                                            <a href="forms-x-editable.html">X Editable</a>
                                        </li>
                                        <li>
                                            <a href="forms-image-crop.html">Image Crop</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarTables" data-bs-toggle="collapse">
                                    <i data-feather="grid"></i>
                                    <span> Tables </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTables">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="tables-basic.html">Basic Tables</a>
                                        </li>
                                        <li>
                                            <a href="tables-datatables.html">Data Tables</a>
                                        </li>
                                        <li>
                                            <a href="tables-editable.html">Editable Tables</a>
                                        </li>
                                        <li>
                                            <a href="tables-responsive.html">Responsive Tables</a>
                                        </li>
                                        <li>
                                            <a href="tables-footables.html">FooTable</a>
                                        </li>
                                        <li>
                                            <a href="tables-bootstrap.html">Bootstrap Tables</a>
                                        </li>
                                        <li>
                                            <a href="tables-tablesaw.html">Tablesaw Tables</a>
                                        </li>
                                        <li>
                                            <a href="tables-jsgrid.html">JsGrid Tables</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarCharts" data-bs-toggle="collapse">
                                    <i data-feather="bar-chart-2"></i>
                                    <span> Charts </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarCharts">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="charts-apex.html">Apex Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-flot.html">Flot Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-morris.html">Morris Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-chartjs.html">Chartjs Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-peity.html">Peity Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-chartist.html">Chartist Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-c3.html">C3 Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-sparklines.html">Sparklines Charts</a>
                                        </li>
                                        <li>
                                            <a href="charts-knob.html">Jquery Knob Charts</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarMaps" data-bs-toggle="collapse">
                                    <i data-feather="map"></i>
                                    <span> Maps </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarMaps">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="maps-google.html">Google Maps</a>
                                        </li>
                                        <li>
                                            <a href="maps-vector.html">Vector Maps</a>
                                        </li>
                                        <li>
                                            <a href="maps-mapael.html">Mapael Maps</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarMultilevel" data-bs-toggle="collapse">
                                    <i data-feather="share-2"></i>
                                    <span> Multi Level </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarMultilevel">
                                    <ul class="nav-second-level">
                                        <li>
                                            <a href="#sidebarMultilevel2" data-bs-toggle="collapse">
                                                Second Level <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="sidebarMultilevel2">
                                                <ul class="nav-second-level">
                                                    <li>
                                                        <a href="javascript: void(0);">Item 1</a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript: void(0);">Item 2</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>

                                        <li>
                                            <a href="#sidebarMultilevel3" data-bs-toggle="collapse">
                                                Third Level <span class="menu-arrow"></span>
                                            </a>
                                            <div class="collapse" id="sidebarMultilevel3">
                                                <ul class="nav-second-level">
                                                    <li>
                                                        <a href="javascript: void(0);">Item 1</a>
                                                    </li>
                                                    <li>
                                                        <a href="#sidebarMultilevel4" data-bs-toggle="collapse">
                                                            Item 2 <span class="menu-arrow"></span>
                                                        </a>
                                                        <div class="collapse" id="sidebarMultilevel4">
                                                            <ul class="nav-second-level">
                                                                <li>
                                                                    <a href="javascript: void(0);">Item 1</a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript: void(0);">Item 2</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li> -->
                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
            <!-- Left Sidebar End -->