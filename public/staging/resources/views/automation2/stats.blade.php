@extends('layouts.automation.frontend')
@section('title', 'Statistics')
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
			 <!--    <li class="nav-item">

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
			            
			    </li> -->
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

		<div class="card mt-4" id="">
        <div class="card-body pb-4">
        	<h3 class="mt-20 mb-20"> Top Engaged Contacts</h3>
         		
  
                		
             	<table class="table-responsive" id="click" style="">
             		<thead>
             			<tr class="text-name">
		             		<th>Email</th>
		             		<!-- <th>Name</th> -->
		             		<th class="swap-buttons">

		             			<button class="w3-bar-item w3-button swapbutton click" id="clickbtn" onclick="swapbutton('Click')">Click</button>
  								<button class="w3-bar-item w3-button swapbutton open" id="openbtn" onclick="swapbutton('Open')">Open</button>
		             		</th>
             			</tr>
             		</thead>
             	
             
             	<tbody id="post_data">
             
             	
             	</tbody>

     
             	
           	</table>
           	<div id="pagination_link"></div>
       </div>
   </div>
	
</div>
<div class="col-md-3">
	<div class="top-fixed" id="subnav">
        <div class="card card-body pb-4">
        	<h3 class="mt-20 mb-20">Top clicked links</h3>
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
<script type="text/javascript">
	$(document).ready(function() {
   $(window).scroll(function() {
       
       var headerH = $('.header').outerHeight(true);
       console.log(headerH);
//this will calculate header's full height, with borders, margins, paddings
       var scrollVal = $(this).scrollTop();
        if ( scrollVal > headerH ) {
            $('#subnav').css({'position':'fixed','top' :'50px'});
        } else {
            $('#subnav').css({'position':'static','top':'50px'});
        }
    });
 });
</script>
<!-- <script type="text/javascript">
	$(document).ready(function() {

    $('#open').DataTable(
    	{
    searching: false,
    ordering:  false,
  scrollX: 400
} 
    	);
} );
</script> -->
<script>
		$(document).ready( function () {
			swapbutton('Click');
} );
 function swapbutton(state,page = 1) {
		       var uid = '{{$uid}}';
		       	if(state=='Click'){
		       		//$('#open').css("display", "none")
		       		
		       		 fetchlogdata(uid,page,state);

		       		$('#click').css("display", "block");
		       	   $('#clickbtn').addClass('click');
		       	   $('#openbtn').removeClass('click');
		       	}else{
		       			 fetchlogdata(uid,page,state);
		       		$('#open').css("display", "block");
		       		  $('#openbtn').addClass('click');
		       	   $('#clickbtn').removeClass('click');
		       	}
	

  }

  function fetchlogdata(uid,page,state){

		       		$.ajax({
					        url: "{{url('automation/statslog')}}/"+uid+"/"+page+"/"+state,
					        type: 'GET',
					        dataType: 'json', // added data type
					        success: function(res) {
					        	//var response = JSON.parse(res);
					        	//console.log(res.data);

			var html = '';

			//var serial_no = 1;

			if(res.data.length > 0)
			{
				for(var count = 0; count < res.data.length; count++)
				{
					html += '<tr>';
					html += '<td>'+res.data[count].value+'</td>';
					html += '<td>'+Math.ceil(res.data[count].countData)+'</td>';
					///html += '<td>'+response.data[count].post_description+'</td>';
					html += '</tr>';
					//serial_no++;
				}
			}
			else
			{
				html += '<tr class="no-found"><td colspan="3" class="text-center">No Data Found</td></tr>';
			}
			//console.log(res.total_data);
			document.getElementById('post_data').innerHTML = html;

			//document.getElementById('total_data').innerHTML = res.total_data;

			document.getElementById('pagination_link').innerHTML = res.pagination;
					            
					            
					        }
					    });
  }
</script>
@endsection