@extends('layouts.backend')

@section('title', trans('messages.dashboard'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/echarts/echarts.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
@endsection

@section('content')

    

    <h1 class="mb-10">{{ trans('messages.backend_dashboard_hello', ['name' => Auth::user()->displayName()]) }}</h1>
    <p>{{ trans('messages.backend_dashboard_welcome') }}</p>

    <div class="row">
        <div class="col-md-6">
            <h3 class="text-semibold"><i class="fe-user"></i> {{ trans('messages.customers_growth') }}</h3>
            @include('admin.customers._growth_chart')
        </div>
        <div class="col-md-6">
            <h3 class="text-semibold"><i class="icon-clipboard3"></i> {{ trans('messages.plans_chart') }}</h3>
            @include('admin.plans._pie_chart')
        </div>
    </div>

    <div class="row mt-30">
        <div class="col-md-6">
            <h3 class="text-semibold">
                <i class="fa fa-hand-pointer-o"></i>
                {{ trans('messages.recent_subscriptions') }}
            </h3>
            <p style="margin-bottom: 30px" class="link-inline">{!! trans('messages.admin.dashboard.recent_subscriptions.wording', [ 'here' => action('Admin\SubscriptionController@index') ]) !!}</p>
            <ul class="modern-listing mt-0 mb-0 top-border-none type2">
                @forelse (Auth::user()->admin->recentSubscriptions() as $subscription)
                    <li class="">
                        <div class="row">
                            <div class="col-sm-5 col-md-5">
                                <h6 class="mt-0 mb-0 text-semibold">
                                    <a href="{{ action('Admin\CustomerController@subscriptions', $subscription->customer->uid) }}">
                                        <i class="icon-clipboard3"></i>
                                        {{ $subscription->plan->name }}
                                    </a>
                                </h6>
                                <p class="mb-0">
                                    <i class="fe-user" style="
                                        font-size: 14px;
                                        padding: 0;
                                        margin: 5px 0 0 -8px;
                                        height: auto;"></i>
                                    {{ $subscription->customer->user->displayName() }}
                                </p>
                            </div>
                            <div class="col-sm-4 col-md-4 text-left">
                                @if ($subscription->isEnded())
                                    <h5 class="no-margin">
                                        <span class="kq_search">{{ Acelle\Library\Tool::formatDate($subscription->ends_at) }}</span>
                                    </h5>
                                    <span class="text-muted2">{{ trans('messages.subscription.ended_on') }}</span>
                                @elseif ($subscription->isActive())
                                    <h5 class="no-margin">
                                        <span class="kq_search">{{ Acelle\Library\Tool::formatDate($subscription->ends_at) }}</span>
                                    </h5>
                                    <span class="text-muted2">{{ trans('messages.subscription.ends_on') }}</span>
                                @else
                                    <h5 class="no-margin">
                                        <span class="kq_search">{{ Acelle\Library\Tool::formatDate($subscription->updated_at) }}</span>
                                    </h5>
                                    <span class="text-muted2">{{ trans('messages.subscription.updated_at') }}</span>
                                @endif
                            </div>
                            <div class="col-sm-3 col-md-3 text-left">
                                <span class="text-muted2 list-status pull-left">
                                    <span class="label label-flat bg-{{ $subscription->status }}">{{ trans('messages.subscription.status.' . $subscription->status) }}</span>
                                </span>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="empty-li">
                        {{ trans('messages.empty_record_message') }}
                    </li>
                @endforelse
            </ul>
        </div>
        <div class="col-md-6">
            <h3 class="text-semibold">
                <i class="fe-user"></i>
                {{ trans('messages.recent_customers') }}
            </h3>
            <p style="margin-bottom: 30px" class="link-inline">{!! trans('messages.admin.dashboard.recent_customers.wording', [ 'here' => action('Admin\CustomerController@index') ]) !!}</p>
            <ul class="modern-listing mt-0 mb-0 top-border-none type2">
                
            </ul>
        </div>
    </div>

    <h3 class="text-semibold">
        <i class="icon-history position-left"></i>
        {{ trans('messages.activities') }}
    </h3>
    <p style="margin-bottom: 30px" class="link-inline">{!! trans('messages.admin.dashboard.recent_activity.wording', [ 'here' => action('Admin\CustomerController@index') ]) !!}</p>
    @if (\Auth::user()->admin->getLogs()->count() == 0)
        <div class="empty-list">
            <i class="icon-history"></i>
            <span class="line-1">
                {{ trans('messages.no_activity_logs') }}
            </span>
        </div>
    @else
        <div class="scrollbar-box action-log-box">
            <!-- Timeline -->
            <div class="timeline timeline-left content-group">
                <div class="timeline-container">
                        @foreach (\Auth::user()->admin->getLogs()->take(20)->get() as $log)
                            <!-- Sales stats -->
                            <div class="timeline-row">
                                <div class="timeline-icon">
                                    <a href="#"><img src="{{ $log->customer->user->getProfileImageUrl() }}" alt=""></a>
                                </div>

                                <div class="panel panel-flat timeline-content">
                                    <div class="panel-heading">
                                        <h6 class="panel-title text-semibold">{{ $log->customer->user->displayName() }}</h6>
                                        <div class="heading-elements">
                                            <span class="heading-text"><i class="position-left text-bold lnr lnr-clock"></i>
                                                @if ($log->created_at)
                                                    @if ($log->created_at->lessThan(\Carbon\Carbon::now()->subMonth(1)))
                                                        {{ \Acelle\Library\Tool::formatDateTime($log->created_at) }}
                                                    @else
                                                        {{ $log->created_at->diffForHumans() }}
                                                    @endif
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="panel-body">
                                        {!! $log->message() !!}
                                    </div>
                                </div>
                            </div>
                            <!-- /sales stats -->
                        @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="sub-section mb-20 mt-30" style="margin-top: 60px">
        <h3 class="text-semibold mt-40">{{ trans('messages.resources_statistics') }}</h3>
        <p>{{ trans('messages.resources_statistics_intro') }}</p>
        <div class="row">
            <div class="col-md-6">
                <ul class="dotted-list topborder section">
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="fe-user"></i> {{ trans('messages.customers') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllCustomers()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li class="selfclear">
                        <div class="unit size1of2">
                            <strong><i class="fa fa-hand-pointer-o"></i> {{ trans('messages.subscriptions') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllSubscriptions()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li class="selfclear">
                        <div class="unit size1of2">
                            <strong><i class="icon-clipboard3"></i> {{ trans('messages.plans') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllPlans()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="fe-list"></i> {{ trans('messages.lists') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllLists()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="fe-user"></i> {{ trans('messages.subscribers') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ number_with_delimiter($subscribersCount) }}</mc:flag>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="dotted-list topborder section">
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="fe-user"></i> {{ trans('messages.admins') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllAdmins()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li class="selfclear">
                        <div class="unit size1of2">
                            <strong><i class="fe-user-plus"></i> {{ trans('messages.admin_groups') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllAdminGroups()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li class="selfclear">
                        <div class="unit size1of2">
                            <strong><i class="fe-server"></i> {{ trans('messages.sending_servers') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllSendingServers()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="fe-globe"></i> {{ trans('messages.sending_domains') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllSendingDomains()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="fe-send"></i> {{ trans('messages.campaigns') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ Auth::user()->admin->getAllCampaigns()->count() }}</mc:flag>
                        </div>
                    </li>
                    <li>
                        <div class="unit size1of2">
                            <strong><i class="icon-alarm-check"></i> {{ trans('messages.automations') }}</strong>
                        </div>
                        <div class="lastUnit size1of2">
                            <mc:flag>{{ number_with_delimiter($automationsCount) }}</mc:flag>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
