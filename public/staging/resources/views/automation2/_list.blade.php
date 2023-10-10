@if ($automations->count() > 0)
    <table class="table  pml-table table-striped"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
    <thead>
        <tr>
            <!-- <th>#</th>
            <th>Name</th>
            <th class="text-center">Prospects</th>
            <th class="text-center">Emails</th>
            <th class="text-center">Complete</th>
            <th></th>
            <th>Total Open</th>
            <th>Total Click</th>
            <th>Total Bounce</th>
            <th>Date</th>
            <th>Paused</th>
            <th>Action</th> -->







            <th></th>
            <th class="text-center">Name</th>
            <th class="text-center">Date</th>
            <th></th>
            <th class="text-center">Prospects</th>
            <!-- <th></th> -->
            <th class="text-center">Total Open</th>
            <th class="text-center">Total Click</th>
            <th class="text-center">Total Bounce</th>
            <!-- <th></th> -->
            <th class="text-center">Status</th>
            <th></th>
            <th></th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    
        @foreach ($automations as $key => $automation)
            @php
            $automationss = getAutomations($automation->id);
          
            @endphp
            @if(count($automationss)>0)
            <tr>
                <td width="1%">
                    <div class="text-nowrap">
                        <div class="checkbox inline">
                            <label>
                                <input type="checkbox" class="node styled"
                                    name="ids[]"
                                    value="{{ $automation->uid }}"
                                />
                            </label>
                        </div>
                    </div>
                </td>
                @php
                $automationss = getAutomations($automation->id);
                $counts = 0;
                $completed = '0.00%' ;
                $complete = 0;
                $created = '';
                $autocount = 0;
                $i = 'A';
                $totalOpensDataA = 0;
                $totalOpensDataB = 0;
                $totalClicksDataA = 0;
                $totalClicksDataB = 0;
                $totalBouncesDataA = 0;
                $totalBouncesDataB = 0;
                
                foreach($automationss as $automatin){
                    $stats = $automatin->readCache('SummaryStats');

                    $totSubs = $stats['total'];

                    $autocount++;
                    if($i == 'A'){
                        $countA = $automatin->countEmails();
                    }else{
                       $countB = $automatin->countEmails(); 
                    }
                    $counts += $automatin->countEmails();
                    $autostats[$i] = $automatin->readCache('SummaryStats') ? $automatin->readCache('SummaryStats')['complete'] : 0;
                    $i++;
                    $complete += $automatin->readCache('SummaryStats') ? $automatin->readCache('SummaryStats')['complete'] : 0;
                    $created = Tool::formatDateTime($automation->created_at);
                    if($key == 0){
                        $totalOpensDataA = $automatin->openLogs();
                        $totalClicksDataA = $automatin->clickLogs();
                        $totalBouncesDataA = $automatin->bounceLogs();
                    }else{
                       $totalOpensDataB = $automatin->openLogs(); 
                       $totalClicksDataB = $automatin->clickLogs();
                       $totalBouncesDataB = $automatin->bounceLogs();
                    }
                }

                $alaphabetData = '';
                if($complete > 0){
                    $newComplete = $complete/$autocount;
                    $completed = number_to_percentage($newComplete);
                    if(count($automationss)>1){
                      if($autostats['A'] >  $autostats['B'] && $countA > 0){
                        $alaphabetData = 'Segment A'; 
                      }elseif($autostats['B'] >  $autostats['A'] && $countB > 0){
                        $alaphabetData = 'Segment B'; 
                      }else{
                        $alaphabetData = 'Segment A'; 
                      }  
                    }
                }
                
                $totalOpensData = $totalOpensDataA + $totalOpensDataB;
                $totalClickData = $totalClicksDataB + $totalClicksDataB;
                $totalBounceData = $totalBouncesDataA + $totalBouncesDataB;
                
                
                
                @endphp
                <td>
                    <h5 class="no-margin text-bold text-center">
                        <a class="kq_search" href="{{ action('Automation2Controller@edit', $automation->uid) }}">
                            {{ $automation->name }}
                        </a>
                    </h5>
                   <div class="text-semibold" data-popup="tooltip">
                        
                    </div>
                </td>
                   <td class="text-center" >
                    <span class="no-margin text-bold ">
                        {{ Tool::formatDate($automation->created_at) }}
                    </span>
                    <!-- <br /> -->
                    <!-- <span class="text-muted">{{ trans('messages.created_at') }}</span> -->
                </td>
  
    
<td width="10%"></td>

                <td class="text-center prospects_td" width="7%">

                    
                   <h5 class="no-margin">
                        {{ $automation->mailList->readCache('SubscriberCount') }}
                    </h5>
                    <span class="text-muted2 prospects_icon"><i class="fe-user"></i><!-- <br>Prospects --></span>
                </td>
<!-- <td></td> -->
                     <td title="Total Opens" class="text-center" width="7%">
                    <h5 class="no-margin text-teal-800 stat-num">{{ format_number($totalOpensData) }}</h5>

                    <span class="text-muted text-center"> <img src="{{asset('assets/images/opened.png')}}" alt="{{ trans('messages.open_rate') }}" width="30px"></span>
                </td>
                <td title="Total Click" class="text-center" width="7%">
                    <h5 class="no-margin text-teal-800 stat-num">{{ format_number($totalClickData) }}</h5>

                    <span class="text-muted text-center"><img src="{{asset('assets/images/clicked.png')}}" alt="{{ trans('messages.click_rate') }}" width="30px"></span>
                </td>

                <td title="Total Bounce" class="text-center" width="8%">
                 <h5 class="no-margin text-teal-800 stat-num">{{ format_number($totalBounceData) }}</h5>

                    <span class="text-muted"><img src="{{asset('assets/images/bounced.png')}}" alt="Bounce Rate" width="30px"></span>
                </td>


            <!-- <td>
                    <h5 class="no-margin text-teal-800 stat-num">
                        {{ $counts }}
                    </h5>
                    <span class="text-muted2">{{ trans('messages.emails') }}</span>
                </td>
                <td>
                    <h5 class="no-margin text-teal-800 stat-num">
                        {{ $completed}}
                    </h5>
                    <span class="text-muted2">{{ trans('messages.complete') }}</span>
                </td> -->
         <!--        <td>
                    @if($alaphabetData != '')
                    <h5 class="no-margin text-teal-800 stat-num">
                      
                        {{$alaphabetData}}
                        
                    </h5>
                    <span class="text-muted2">Performing Better</span>
                    @endif
                </td> -->
           
             <!-- <td></td> -->
                @php
                if($automation->status=='active'){
                $status = 'Running';
                 }else{
                  $status = 'Paused';
             }
                @endphp
                <td class="text-center inactiv-in" width="7%">
                    <span class="text-muted2 list-status">
                        <span class="label label-flat bg-{{ $automation->status }}"><i class="fa fa-circle" aria-hidden="true"></i>
                        <div class="paused">{{$status}}</div>
                        </span>
                    </span>
                </td>
<td></td>

<td></td>

                <td class="text-right text-nowrap">
                   
                   
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle  bg-grey btn-icon" data-toggle="dropdown">Action <span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                
                                    <!-- <li>
                                        <a data-popup="tooltip" href="{{ action('Automation2Controller@edit', $automation->uid) }}" type="button" class="">
                                            <i class="icon-pencil"></i> Edit
                                        </a>
                                    </li> -->
                                    <li class="user_fristicon">
                                        <a                                            
                                            href="{{ action('Automation2Controller@stats', [
                                                "uid" => $automation->uid
                                            ]) }}"
                                        >
                                            <i class="fa-solid fa-chart-line"></i> Stats
                                        </a>
                                    </li>
                                    <li class="user_fristicon">
                                        <a                                            
                                            href="{{ action('Automation2Controller@subscribers', [
                                                "uid" => $automation->uid
                                            ]) }}"
                                        >
                                            <i class="fe-user"></i> Contacts
                                        </a>
                                    </li>
                                
                                    <li class="user_fristicon">
                                        <a data-method="PATCH" link-confirm="{{ trans('messages.enable_automations_confirm') }}"
                                            href="{{ action('Automation2Controller@enable', ["uids" => $automation->uid]) }}"
                                        >
                                           <i class="fe-check"></i>{{ trans('messages.enable') }}
                                        </a>
                                    </li>
                                
                                    <li class="user_fristicon">
                                        <a data-method="PATCH" link-confirm="{{ trans('messages.disable_automations_confirm') }}"
                                            href="{{ action('Automation2Controller@disable', ["uids" => $automation->uid]) }}"
                                        >
                                            <i class="fa-solid fa-ban"></i> {{ trans('messages.disable') }}
                                        </a>
                                    </li>
                                
                                    <li class="user_fristicon">
                                        <a data-method='delete' delete-confirm="{{ trans('messages.delete_automations_confirm') }}"
                                            href="{{ action('Automation2Controller@delete', ["uids" => $automation->uid]) }}"
                                        >
                                            <i class="fe-trash"></i> {{ trans("messages.delete") }}
                                        </a>
                                    </li>

                                
                            </ul>
                        </div>
                    
                </td>
            </tr>
            @endif
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $automations])
    
@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-paperplane"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-alarm-check"></i>
        <span class="line-1">
            {{ trans('messages.automation_empty_line_1') }}
        </span>
    </div>
@endif
