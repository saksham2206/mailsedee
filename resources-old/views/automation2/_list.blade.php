@if ($automations->count() > 0)

    <table class="table  pml-table table-striped"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
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
                foreach($automationss as $automatin){
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
                @endphp
                <td>
                    <h5 class="no-margin text-bold">
                        <a class="kq_search" href="{{ action('Automation2Controller@edit', $automation->uid) }}">
                            {{ $automation->name }}
                        </a>
                    </h5>
                   <div class="text-semibold" data-popup="tooltip">
                        
                    </div>
                </td>
                <td>

                    
                   <h5 class="no-margin">
                        {{ $automation->mailList->readCache('SubscriberCount') }}
                    </h5>
                    <span class="text-muted2">{{ trans('messages.automation.overview.contacts') }}</span>
                </td>
                <td>

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
                </td>
                <td>
                    @if($alaphabetData != '')
                    <h5 class="no-margin text-teal-800 stat-num">
                        
                        {{$alaphabetData}}
                        
                    </h5>
                    <span class="text-muted2">Performing Better</span>
                    @endif
                </td>
                <td>
                    <h5 class="no-margin text-teal-800 stat-num">{{ $automation->mailList->openUniqRate() }}%</h5>
                    <div class="progress progress-xxs">
                        <div class="progress-bar progress-bar-info" style="width: {{ $automation->mailList->readCache('UniqOpenRate', 0) }}%">
                        </div>
                    </div>
                    <span class="text-muted">{{ trans('messages.open_rate') }}</span>
                </td>
                <td>
                    <h5 class="no-margin text-teal-800 stat-num">{{ $automation->mailList->readCache('ClickedRate', 0) }}%</h5>
                    <div class="progress progress-xxs">
                        <div class="progress-bar progress-bar-info" style="width: {{ $automation->mailList->readCache('ClickedRate', 0) }}%">
                        </div>
                    </div>
                    <span class="text-muted">{{ trans('messages.click_rate') }}</span>
                </td>

                <td>
                    <h5 class="no-margin text-teal-800 stat-num">{{ getBounceData($automation->id) }}%</h5>
                    <div class="progress progress-xxs">
                        <div class="progress-bar progress-bar-info" style="width: {{ getBounceData($automation->id) }}%">
                        </div>
                    </div>
                    <span class="text-muted">Bounce Rate</span>
                </td>
                <td>
                    <span class="no-margin text-bold">
                        {{ Tool::formatDateTime($automation->created_at) }}
                    </span>
                    <br />
                    <span class="text-muted">{{ trans('messages.created_at') }}</span>
                </td>
                <td class="text-center">
                    <span class="text-muted2 list-status">
                        <span class="label label-flat bg-{{ $automation->status }}">{{ trans('messages.automation.status.' . $automation->status) }}</span>
                    </span>
                </td>
                <td class="text-right text-nowrap">
                   
                   
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle  bg-grey btn-icon" data-toggle="dropdown">Action <span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                
                                    <li>
                                        <a data-popup="tooltip" href="{{ action('Automation2Controller@edit', $automation->uid) }}" type="button" class="">
                                            <i class="icon-pencil"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a                                            
                                            href="{{ action('Automation2Controller@stats', [
                                                "uid" => $automation->uid
                                            ]) }}"
                                        >
                                            <i class="icon-users"></i> Stats
                                        </a>
                                    </li>
                                    <li>
                                        <a                                            
                                            href="{{ action('Automation2Controller@subscribers', [
                                                "uid" => $automation->uid
                                            ]) }}"
                                        >
                                            <i class="icon-users"></i> {{ trans('messages.subscribers') }}
                                        </a>
                                    </li>
                                
                                    <li>
                                        <a data-method="PATCH" link-confirm="{{ trans('messages.enable_automations_confirm') }}"
                                            href="{{ action('Automation2Controller@enable', ["uids" => $automation->uid]) }}"
                                        >
                                            <i class="icon-checkbox-checked2"></i> {{ trans('messages.enable') }}
                                        </a>
                                    </li>
                                
                                    <li>
                                        <a data-method="PATCH" link-confirm="{{ trans('messages.disable_automations_confirm') }}"
                                            href="{{ action('Automation2Controller@disable', ["uids" => $automation->uid]) }}"
                                        >
                                            <i class="icon-checkbox-unchecked2"></i> {{ trans('messages.disable') }}
                                        </a>
                                    </li>
                                
                                    <li>
                                        <a data-method='delete' delete-confirm="{{ trans('messages.delete_automations_confirm') }}"
                                            href="{{ action('Automation2Controller@delete', ["uids" => $automation->uid]) }}"
                                        >
                                            <i class="icon-trash"></i> {{ trans("messages.delete") }}
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
