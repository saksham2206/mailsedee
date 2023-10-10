
                

<style type="text/css">
    .accordion {
    background-color: #3a21f4;
    color: #fff;
    cursor: pointer;
    padding: 18px;
    width: 100%;
    border: none;
    text-align: left;
    outline: none;
    font-size: 15px;
    transition: 0.4s;
    border-bottom: 5px solid #7160ef;
    border-radius: 7px;
    margin-bottom: 10px;
}

.active, .accordion:hover {
    background-color: #fe4c04;
    border-bottom: 5px solid #fbaeae;
}

.panel1 {
  padding: 0 18px;
  display: none;
  background-color: white;
  overflow: hidden;
}
</style>   
<div class="insight-topine flex small">
    <div class="insight-desc mr-auto">
        {{ trans('messages.automation.your_overview') }}
    </div>
    <div class="insight-time">
        
    </div>
</div>
<br>
@php

    $alpabet = 'A';
    //$alphabetArray = array();
  //  dd($automations);
    @endphp
@foreach($automations as $key => $automation)  
 <?php
// dd($automation['id'], $key);

 $automationss = getAutomations($automation['main_id']);
//  print_r($automationss);
// echo($automation['main_id']);
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
              //  dd($alaphabetData);
                ?>

@if(count($insight1[$key])>1 )

<button class="accordion"><strong>Segment {{$alpabet}} </strong><i class="fa fa-2x fa-caret-down" style="float:right;"></i></button>
@php
$alpabet++;
$total =  format_number($stats1[$key]['total']);

@endphp
<div class="panel1">
<div class="insight-stat-brief d-flex mt-3 mb-4">
    <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
       class="xtooltip insight-stat-col flex-fill">
        <number>{{ format_number($stats1[$key]['total']) }}</number>
        <desc class="text-muted text-center">
            <span class="stats-title">{{ trans_choice('messages.automation.contacts', $stats1[$key]['total']) }}</span>
        </desc>
    </a>
 <!--    <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
      onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
        'uid' => $automation->uid,
      ]) }}')" class="xtooltip insight-stat-col flex-fill">
        <number>{{ number_with_delimiter($stats1[$key]['involed']) }}</number>
        <desc class="text-muted text-center">
            <span class="stats-title">{{ trans_choice('messages.automation.involved_new', $stats1[$key]['total']) }}</span>
        </desc>
    </a> -->
       <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
      class="xtooltip insight-stat-col flex-fill">
        <number>{{ number_with_delimiter($stats1[$key]['involed']) }}</number>
        <desc class="text-muted text-center">
            <span class="stats-title">{{ trans_choice('messages.automation.involved_new', $stats1[$key]['total']) }}</span>
        </desc>
    </a>
    <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
       class="xtooltip insight-stat-col flex-fill">
        <number>{{ number_to_percentage($stats1[$key]['complete']) }}</number>
        <desc class="text-muted text-center">
            <span class="stats-title">{{ trans_choice('messages.automation.complete_percent', $stats1[$key]['total']) }}</span>
        </desc>
    </a>
</div>
  
<p class="insight-intro">
    {{ trans('messages.automation.insight.intro') }}
</p>
    
<div class="mc-table small mt-3">
    
    @foreach ($insight1[$key] as $key => $element)
        @php
            $action = $automation->getElement($key);
           // echo $key;
        @endphp
        
            <div class="mc-row mc-border d-flex align-items-center">
                <div class="media trigger">
                    {!! $action->getIcon() !!}
                </div>
                <div class="flex-fill" style="width: 35%">
                    <label title="{{ trans('messages.automation.go_contacts') }}"  class="cursor-pointer font-weight-semibold"
                    >
                        {{ $action->getName() }}
                    </label>
                    <desc title="{{ trans('messages.automation.go_contacts') }}" class="cursor-pointer">
                        {{ $element['subtitle'] }}
                    </desc>
                </div>
                @if(array_key_exists('deliver',$element))
                @php
                    if($total>0){
              
                   $deliver_rate = ($element['deliver']*100)/$total;
                    $open_rate = ($element['open']*100)/$total;
                    $click_rate = ($element['click']*100)/$total;
                    //dd($deliver_rate,$open_rate,$click_rate);  
                }else{
              
                $deliver_rate = 0;
                $open_rate = 0;
                $click_rate = 0;
            }
                   
                @endphp
                <div class="flex-fill mailed-icon" >
                    <span class="text-muted text-center" title="Delivered Rate"> <img src="{{url('assets/images/icon-recived.png')}}" alt="recived rate" width="30px"><h5 class="no-margin text-teal-800 stat-num">{{format_number($deliver_rate)}}%</h5></span>
                    
                    <span class="text-muted text-center"  title="Open rate"> <img src="{{url('assets/images/opened.png')}}" alt="Open rate" width="30px">  <h5 class="no-margin text-teal-800 stat-num">{{format_number($open_rate)}}%</h5></span> 
                  
                    <span class="text-muted text-center"  title="Click rate"><img src="{{url('assets/images/clicked.png')}}" alt="Click rate" width="30px"><h5 class="no-margin text-teal-800 stat-num">{{format_number($click_rate)}}%</h5></span>
                    
                    
                    
                </div>
                @else
                <div class="flex-fill mailed-icon"></div>
                @endif
                <!-- <a 
                    title="{{ trans('messages.automation.go_timeline') }}"
                    href="javascript:;"
                    onclick="timelinePopup.load('{{ action('Automation2Controller@timeline', [
                        'uid' => $automation->uid,
                      ]) }}')"
                    class="flex-fill"
                > -->
                <desc>{{ trans('messages.automation.action.last_updated') }}

                     <br>
                 {{ \Carbon\Carbon::parse($element['latest_activity'])->diffForHumans() }}
                </desc>


                    <label class="font-weight-semibold">
                       
                    </label>
                    
                </a>
                <div class="flex-fill text-right">
                    <h3 title="{{ trans('messages.automation.insight.percent_tip') }}"  class="cursor-pointer font-weight-semibold"
                    >
                        {{ number_to_percentage($element['percentage']) }}
                    </h3>
                </div>
            </div>
            
    @endforeach
</div>
<br>

</div>
@endif
@endforeach 

@php

    $alpabet = 'A';
    //$alphabetArray = array();
    @endphp
@foreach($automations as $key => $automation)  
@if(count($insight1[$key])>1 )

<button class="accordion"><strong>Segment {{$alpabet}} Performance  </strong><i class="fa fa-2x fa-caret-down" style="float:right;"></i></button>
@php
$alpabet++;
$total =  format_number($stats1[$key]['total']);
@endphp
<div class="panel1">

    
<div class="mc-table small mt-3">
    
    @foreach ($insight1[$key] as $key => $element)
        @php
            $action = $automation->getElement($key);
            //dd($action);
           // echo $key;
        @endphp
            @if(array_key_exists('deliver',$element))
            <div class="mc-row mc-border d-flex align-items-center">
                <div class="media trigger">
                    {!! $action->getIcon() !!}
                </div>
                <div class="flex-fill" style="width: 35%">
                    <label title="{{ trans('messages.automation.go_contacts') }}"  class="cursor-pointer font-weight-semibold"
                    >
                        {{ $action->getName() }}
                    </label>
                    <desc title="{{ trans('messages.automation.go_contacts') }}" class="cursor-pointer">
                        {{ $element['subtitle'] }}
                    </desc>
                </div>
                @if(array_key_exists('deliver',$element))
                @php
                      if($total>0){
                   $deliver_rate = ($element['deliver']*100)/$total;
                    $open_rate = ($element['open']*100)/$total;
                    $click_rate = ($element['click']*100)/$total;
                    //dd($deliver_rate,$open_rate,$click_rate);  
                }else{
                $deliver_rate = 0;
                $open_rate = 0;
                $click_rate = 0;
            }
                   
                @endphp
                <div class="flex-fill mailed-icon" >
                    <span class="text-muted text-center" title="Delivered Rate"> <img src="{{url('assets/images/icon-recived.png')}}" alt="recived rate" width="30px"><h5 class="no-margin text-teal-800 stat-num">{{format_number($deliver_rate)}}%</h5></span>
                    
                    <span class="text-muted text-center"  title="Open rate"> <img src="{{url('assets/images/opened.png')}}" alt="Open rate" width="30px"><h5 class="no-margin text-teal-800 stat-num">{{format_number($open_rate)}}%</h5></span>
                    
                    <span class="text-muted text-center"  title="Click rate"><img src="{{url('assets/images/clicked.png')}}" alt="Click rate" width="30px"><h5 class="no-margin text-teal-800 stat-num">{{format_number($click_rate)}}%</h5></span>
                    
                    
                    
                </div>
                @else
                <div class="flex-fill mailed-icon"></div>
                @endif
                <!-- <a 
                    title="{{ trans('messages.automation.go_timeline') }}"
                    href="javascript:;"
                    onclick="timelinePopup.load('{{ action('Automation2Controller@timeline', [
                        'uid' => $automation->uid,
                      ]) }}')"
                    class="flex-fill"
                > -->
                    <label class="font-weight-semibold">
                        {{ \Carbon\Carbon::parse($element['latest_activity'])->diffForHumans() }}
                    </label>
                    <desc>{{ trans('messages.automation.action.last_updated') }}</desc>
                </a>
                <div class="flex-fill text-right">
                    <h3 title="{{ trans('messages.automation.insight.percent_tip') }}" class="cursor-pointer font-weight-semibold"
                    >
                        {{ number_to_percentage($element['percentage']) }}
                    </h3>
                </div>
            </div>
            @endif
            
    @endforeach
</div>
<br>

</div>
@endif
@endforeach 
<td>
                    @if($alaphabetData != '')
                    <h5 class="no-margin text-teal-800 stat-num">
                      
                        {{$alaphabetData}}
                        
                    </h5>
                    <span class="text-muted2">Performing Better</span>
                    @endif
                </td>
    <script>
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.display === "block") {
      panel.style.display = "none";
    } else {
      panel.style.display = "block";
    }
  });
}
</script>
