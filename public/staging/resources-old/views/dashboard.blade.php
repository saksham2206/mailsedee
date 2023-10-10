@extends('layouts.frontend')

@section('title', trans('messages.dashboard'))

@section('page_script')
     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>     
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/echarts/echarts.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
@endsection

@section('content')
   
                            <div class="page-title">
                           
                                <h1>
                                    <span class="text-semibold">{!! trans('messages.frontend_dashboard_hello', ['name' => Auth::user()->displayName()]) !!}</span>
                                </h1>
                                    <p>{!! trans('messages.frontend_dashboard_welcome') !!}</p>
                            </div>
    
                            <div class="row">
                                <div class="col-lg-3 col-xl-3">
                                    <div class="card bg-pattern">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="avatar-md bg-blue rounded">
                                                        <i class="fe-layers avatar-title font-22 text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-end">
                                                        <h3 class="text-dark my-1">
                                                            <span data-plugin="counterup">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->getSendingQuotaUsage()) }}/{{ (Auth::user()->customer->getSendingQuota() == -1) ? '∞' : \Acelle\Library\Tool::format_number(Auth::user()->customer->getSendingQuota()) }}</span>
                                                        </h3>
                                                        <p class="text-muted mb-0 text-truncate">{{ trans('messages.sending_quota') }}</p>
                                                    </div>
                                                </div>
                                                  <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 100%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->displaySendingQuotaUsage() }}</span>
                                                    </div>
                                                </div>
                                               <!--   <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-{{ Auth::user()->customer->getSendingQuotaUsagePercentage() >= 80 ? 'danger' : 'success' }}" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: {{ Auth::user()->customer->displaySendingQuotaUsage() }}" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->displaySendingQuotaUsage() }}</span>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div> <!-- end card-->
                                </div> <!-- end col -->

                                <div class="col-lg-3 col-xl-3">
                                    <div class="card bg-pattern">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="avatar-md bg-success rounded">
                                                        <i class="fe-award avatar-title font-22 text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-end">
                                                        <h3 class="text-dark my-1"><span data-plugin="counterup">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->listsCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxLists()) }}</span></h3>
                                                        <p class="text-muted mb-0 text-truncate">{{ trans('messages.list') }}</p>
                                                    </div>
                                                </div>
                                                  <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-success" role="progressbar" aria-valuenow="{{ Auth::user()->customer->listsUsage() }}" aria-valuemin="0" aria-valuemax="100" style="width: 100%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->listsUsage() }}</span>
                                                    </div>
                                                </div>
                                               <!--   <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-{{ Auth::user()->customer->listsUsage() >= 80 ? 'danger' : 'success' }}" role="progressbar" aria-valuenow="{{ Auth::user()->customer->listsUsage() }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ Auth::user()->customer->listsUsage() }}%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->listsUsage() }}</span>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div> <!-- end card-->
                                </div> <!-- end col -->
                                <div class="col-lg-3 col-xl-3">
                                    <div class="card bg-pattern">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="avatar-md bg-danger rounded">
                                                        <i class="fe-delete avatar-title font-22 text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-end">
                                                        <h3 class="text-dark my-1"><span data-plugin="counterup">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->campaignsCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxCampaigns()) }}</span></h3>
                                                        <p class="text-muted mb-0 text-truncate">{{ trans('messages.campaign') }}</p>
                                                    </div>
                                                </div>
                                                 <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="{{ Auth::user()->customer->campaignsUsage() }}" aria-valuemin="0" aria-valuemax="100" style="width: 100%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->campaignsUsage() }}</span>
                                                    </div>
                                                </div>
                                                <!--  <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-{{ Auth::user()->customer->campaignsUsage() >= 80 ? 'danger' : 'success' }}" role="progressbar" aria-valuenow="{{ Auth::user()->customer->campaignsUsage() }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ Auth::user()->customer->campaignsUsage() }}%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->campaignsUsage() }}</span>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div> <!-- end card-->
                                </div> <!-- end col -->
                                <div class="col-lg-3 col-xl-3">
                                    <div class="card bg-pattern">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="avatar-md bg-warning rounded">
                                                        <i class="fe-users avatar-title font-22 text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-end">
                                                        <h3 class="text-dark my-1"><span data-plugin="counterup">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->readCache('SubscriberCount', 0)) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxSubscribers()) }}</span></h3>
                                                        <p class="text-muted mb-0 text-truncate">{{ trans('messages.subscriber') }}</p>
                                                    </div>
                                                </div>
                                                  <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="{{ Auth::user()->customer->campaignsUsage() }}" aria-valuemin="0" aria-valuemax="100" style="width: 100%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->readCache('SubscriberUsage', 0) }}%</span>
                                                    </div>
                                                </div>
                                              <!--   <div class="progress progress-sm" style="height: 05px;padding: 0px;">
                                                    <div class="progress-bar bg-{{ Auth::user()->customer->subscribersUsage() >= 80 ? 'danger' : 'success' }}" role="progressbar" aria-valuenow="{{ Auth::user()->customer->campaignsUsage() }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ Auth::user()->customer->readCache('SubscriberUsage', 0) }}%" title="{{ Auth::user()->customer->displaySendingQuotaUsage() }}">
                                                        <span class="visually-hidden">{{ Auth::user()->customer->readCache('SubscriberUsage', 0) }}%</span>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div> <!-- end card-->
                                </div> <!-- end col -->
                            </div>
                            <!-- end row-->


                        
                            <div class="card">
                                <div class="card-body pb-2">

                                 <h3 class=" mt-40"><i class="icon-paperplane"></i> {{ trans('messages.recently_sent_campaigns_new') }}</h3>
                                       
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @include('helpers.form_control', [
                                                        'type' => 'automation_select',
                                                        'class' => 'dashboard-campaign-select',
                                                        'name' => 'automations_id',
                                                        'label' => '',
                                                        'value' => '',
                                                        'options' => Acelle\Model\AutomationList::AutomationListOption(Auth::user()->customer->id),
                                                    ])
                                                </div>
                                            </div>
                                            <div class="campaign-quickview-container" data-url="{{ url('automation/quickView') }}"></div>
                                        
                               </div>
                           </div>

                               <div class="row">
                                <div class="col-xl-6">
                                    <div class="card">
                                       <div class="card-body pb-2">
                                          
                                                <div    id="chart"></div>
                                       </div>
                                    </div> <!-- end card -->
                                </div> <!-- end col-->

                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="float-end d-none d-md-inline-block">
                                                <div class="btn-group mb-2">
                                                  <!--   <button type="button" class="btn btn-xs btn-light">Today</button>
                                                    <button type="button" class="btn btn-xs btn-light">Weekly</button>
                                                    <button type="button" class="btn btn-xs btn-secondary">Monthly</button> -->
                                                </div>
                                            </div>
        
                                            <h4 class="header-title mb-3">Subscribed</h4>
                                            <div dir="ltr">
                                                <div id="sales-analytics" class="apex-charts" data-colors="#5671f0,#1abc9c"></div>
                                            </div>
                                        </div>
                                    </div> <!-- end card -->
                                </div> <!-- end col-->
                            </div>
                            <!-- end row -->
                  <!--        <div class="card">
                                <div class="card-body">
                         <h3 class=" mt-40"><i class="icon-history"></i> {{ trans('messages.activity_log') }}</h3>

                            @if (Auth::user()->customer->logs()->count() == 0)
                                <div class="empty-list">
                                    <i class="icon-history"></i>
                                    <span class="line-1">
                                        {{ trans('messages.no_activity_logs') }}
                                    </span>
                                </div>
                            @else
                                <div class="scrollbar-box action-log-box">
                                 
                                    <div class="timeline timeline-left content-group">
                                        <div class="timeline-container">
                                                @foreach (Auth::user()->customer->logs()->take(20)->get() as $log)
                                             
                                                    <div class="timeline-row">
                                                        <div class="timeline-icon">
                                                            <a href="#"><img src="{{ $log->customer->user->getProfileImageUrl() }}" alt=""></a>
                                                        </div>

                                                        <div class="panel panel-flat timeline-content">
                                                            <div class="panel-heading">
                                                                <h6 class="panel-title text-semibold">{{ $log->customer->user->displayName() }}</h6>
                                                                <div class="heading-elements">
                                                                    <span class="heading-text"><i class="icon-history position-left text-success"></i> {{ Tool::dateTime($log->created_at)->diffForHumans() }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="panel-body">
                                                                {!! $log->message() !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                  
                                                @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div> -->

                <!-- Footer Start -->
           <!--      <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>document.write(new Date().getFullYear())</script> &copy; 
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end footer-links d-none d-sm-block">
                                    <a href="javascript:void(0);">About Us</a>
                                    <a href="javascript:void(0);">Help</a>
                                    <a href="javascript:void(0);">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer> -->
                <!-- end Footer -->

         <!--    </div> -->


  

<script> 

      
        var options = {
          series: [{
          name: 'Subscribers',
          data: ['{{$subscribers_count[0]}}','{{$subscribers_count[1]}}','{{$subscribers_count[2]}}','{{$subscribers_count[3]}}','{{$subscribers_count[4]}}','{{$subscribers_count[5]}}','{{$subscribers_count[6]}}']
        }],
          chart: {
          height: 350,
          type: 'bar',
        },
        plotOptions: {
          bar: {
            borderRadius: 10,
            dataLabels: {
              position: 'top', // top, center, bottom
            },
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val ;
          },
          offsetY: -20,
          style: {
            fontSize: '12px',
            colors: ["#000000"]
          }
        },
        
        xaxis: {
          categories: ['{{$subscribers_dates[0]}}','{{$subscribers_dates[1]}}','{{$subscribers_dates[2]}}','{{$subscribers_dates[3]}}','{{$subscribers_dates[4]}}','{{$subscribers_dates[5]}}','{{$subscribers_dates[6]}}'],
          position: 'top',
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          crosshairs: {
            fill: {
              type: 'gradient',
              gradient: {
                colorFrom: '#D8E3F0',
                colorTo: '#BED1E6',
                stops: [0, 100],
                opacityFrom: 0.4,
                opacityTo: 0.5,
              }
            }
          },
          tooltip: {
            enabled: true,
          }
        },
        yaxis: {
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false,
          },
          labels: {
            show: false,
           
            formatter: function (val) {
              return val ;
            }
          }
        
        },
        title: {
          text: 'Last seven days subscriber',
          floating: true,
          offsetY: 330,
          align: 'center',
          style: {
            color: '#444'
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();



      
      var options1 = {
              series: [{{$subscribeduser}},{{$unsubscribeduser}}],
              chart: {
              width: 380,
              type: 'pie',
              color : ['#744be5', '#fdaf06', '#1607fc'],
            },
            labels: ['Subscribed', 'Unsubscribed'],
            responsive: [{
              breakpoint: 480,
              options: {
                chart: {
                  width: 200
                },
                legend: {
                  position: 'bottom'
                }
              }
            }]
            };

            var chart1 = new ApexCharts(document.querySelector("#sales-analytics"), options1);
            chart1.render();

</script>
@endsection
