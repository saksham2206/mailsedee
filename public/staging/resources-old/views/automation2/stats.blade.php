@extends('layouts.automation.frontend')
@section('title', 'Statistics'))
@section('page_script')
     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>     
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/echarts/echarts.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
@endsection
@section('content')
<div class="col-md-9">
	<div class="card">
        <div class="card-body pb-2">
			<ul class="nav nav-tabs mt-3 mb-4">
				<li class="nav-item">
			        <a class="nav-link insight" href="javascript:;" onclick="newSideBar.load('{{ action('Automation2Controller@insight', $AutomationList->uid) }}')">
			            <i class="material-icons mr-2">bubble_chart</i>
			            {{ trans('messages.automation.insight') }}
			        </a>
			    </li>
			    <li class="nav-item">
			        @if ($automation->getTrigger()->getOption('type') == 'woo-abandoned-cart')
			            <a href="javascript:;"
			                onclick="timelinePopup.load('{{ action('Automation2Controller@cartStats', $AutomationList->uid) }}')"
			                class="nav-link statistics">
			                <i class="material-icons mr-2">multiline_chart</i>
			                {{ trans('messages.automation.statistics') }}
			            </a>
			        @else
			            <a href="javascript:;"
			                onclick="timelinePopup.load('{{ action('Automation2Controller@contacts', $AutomationList->uid) }}')"
			                class="nav-link statistics">
			                <i class="material-icons mr-2">multiline_chart</i>
			                {{ trans('messages.automation.statistics') }}
			            </a>
			        @endif
			        <script>
			            $('.nav-link.statistics').on('click', function(e) {
			                e.preventDefault();

			                var link = $(this);

			                setTimeout(function() {
			                    link.removeClass('active');
			                }, 100);
			            });
			        </script>
			            
			    </li>
			</ul>
			<div id="dataPanel">
				
			</div>
		</div>
	</div>
	<div class="card">
        <div class="card-body pb-2">

         			
                    <div class="campaign-quickview-container" data-url="{{ url('automation/quickView') }}"></div>
                
       </div>
   </div>
	<script>
	    @if (isset($tab))
	        $('.nav-link.{{ $tab }}').addClass('active');
	    @endif
	    @if (isset(request()->type))
	        $('.dropdown-item.contacts_{{ request()->type }}').addClass('active');
	    @endif
	    @if (isset($sub))
	        $('.dropdown-item.{{ $sub }}').addClass('active');
	    @endif
	    var newSideBar = new Box($("#dataPanel"));
	    	// timeline popup
	        var timelinePopup = new Popup(undefined, undefined, {
	            onclose: function() {
	                // sidebar.load();
	            }
	        });

	        // popup
	        var popup = new Popup(undefined, undefined, {
	            onclose: function() {
	                newSideBar.load();
	            }
	        });
	    $(document).ready(function(){
	    	$(".insight").click();
	    	var campaign_quickview_container = new Box($('.campaign-quickview-container'));
	    	dashboardQuickview('{{$AutomationList->uid}}',$('.campaign-quickview-container'));

	    })
	</script>
	
</div>
<div class="col-md-3">
	<div class="card">
        <div class="card-body pb-2">
        	<h3 class="mt-40 mb-20"><i class="icon-stats-dots"></i> Top Click Links</h3>
         		@if(count($topClick)>0)		
             <ul>
             	@foreach($topClick as $topClicks)
             	<li><a href="{{$topClicks->url}}" target="_blank" style="font-size: 17px;">{{$topClicks->url}}</a></li>
             	@endforeach
             </ul>  
             @else
             <h6>No Links Available</h6>
             @endif
                
       </div>
   </div>

   
</div>	
@endsection