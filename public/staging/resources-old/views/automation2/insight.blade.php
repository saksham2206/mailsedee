
                

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
    @endphp
@foreach($automations as $key => $automation)  
<button class="accordion"><strong>Segment {{$alpabet}} </strong><i class="fa fa-2x fa-caret-down" style="float:right;"></i></button>
@php
$alpabet++;
@endphp
<div class="panel1">
<div class="insight-stat-brief d-flex mt-3 mb-4">
    <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
      onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
        'uid' => $automation->uid,
        'type' => 'in_action',
      ]) }}')" class="xtooltip insight-stat-col flex-fill">
        <number>{{ format_number($stats1[$key]['total']) }}</number>
        <desc class="text-muted text-center">
            <span class="stats-title">{{ trans_choice('messages.automation.contacts', $stats1[$key]['total']) }}</span>
        </desc>
    </a>
    <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
      onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
        'uid' => $automation->uid,
      ]) }}')" class="xtooltip insight-stat-col flex-fill">
        <number>{{ number_with_delimiter($stats1[$key]['involed']) }}</number>
        <desc class="text-muted text-center">
            <span class="stats-title">{{ trans_choice('messages.automation.involved', $stats1[$key]['total']) }}</span>
        </desc>
    </a>
    <a title="{{ trans('messages.automation.go_contacts') }}" href="javascript:;"
      onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
        'uid' => $automation->uid,
        'type' => 'in_action',
      ]) }}')" class="xtooltip insight-stat-col flex-fill">
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

        @endphp
        
            <div class="mc-row mc-border d-flex align-items-center">
                <div class="media trigger">
                    {!! $action->getIcon() !!}
                </div>
                <div class="flex-fill" style="width: 35%">
                    <label title="{{ trans('messages.automation.go_contacts') }}" onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
                        'uid' => $automation->uid,
                      ]) }}')" class="cursor-pointer font-weight-semibold"
                    >
                        {{ $action->getName() }}
                    </label>
                    <desc title="{{ trans('messages.automation.go_contacts') }}" onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
                        'uid' => $automation->uid,
                      ]) }}')" class="cursor-pointer">
                        {{ $element['subtitle'] }}
                    </desc>
                </div>
                <a 
                    title="{{ trans('messages.automation.go_timeline') }}"
                    href="javascript:;"
                    onclick="timelinePopup.load('{{ action('Automation2Controller@timeline', [
                        'uid' => $automation->uid,
                      ]) }}')"
                    class="flex-fill"
                >
                    <label class="font-weight-semibold">
                        {{ \Carbon\Carbon::parse($element['latest_activity'])->diffForHumans() }}
                    </label>
                    <desc>{{ trans('messages.automation.action.last_updated') }}</desc>
                </a>
                <div class="flex-fill text-center">
                    <h3 title="{{ trans('messages.automation.insight.percent_tip') }}" onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', [
                        'uid' => $automation->uid,
                      ]) }}')" class="cursor-pointer font-weight-semibold"
                    >
                        {{ number_to_percentage($element['percentage']) }}
                    </h3>
                </div>
            </div>
            
    @endforeach
</div>
</div>
@endforeach 

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
