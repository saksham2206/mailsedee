
<h3 class="mt-40 mb-20"><!-- <i class="icon-stats-dots1"></i> --> {{ trans('messages.statistics') }}</h3>
<!-- <div class="sub-h3">{!! trans('messages.campaign_table_chart_intro') !!}</div> -->
<div class="row">

    <div class="col-md-12">
        <div class="panel panel-flat">
            <div class="panel-body">
                
                <div class="chart-container">
                        
                        <div class="col-md-12">
                          <div class="row">
                          <div class="badge-row " id="segmentAopenstatus">
                            
                        </div>
                          <div class="badge-row " id="segmentBopenstatus">       
                        </div>
                      </div>
                           <div class="chart1 has-fixed-height" id="chart3"  data-url="{{ url('automation/chart')}}/{{$main_id}}"></div>
                        </div>
                   
                     <!--  <div class="col-md-6"> -->
                       
                      </div>

                    <div class="col-md-12" >
                       <div class="row">
                       <div class="badge-row " id="segmentAdelivertatus">
                             
                        </div>

                         <div class="badge-row " id="segmentBdelivertatus">       
                        </div>
                      </div>
                      <div class="chart1 has-fixed-height" id="chart4"></div>
                    </div>

                    <div class="col-md-12" >
                       <div class="row">
                       <div class="badge-row " id="segmentAClicks">
                             
                        </div>

                         <div class="badge-row " id="segmentBClicks">       
                        </div>
                      </div>
                      <div class="chart1 has-fixed-height" id="chart5"></div>
                    </div>

                    <div class="col-md-12" >
                       <div class="row">
                       <div class="badge-row " id="segmentABounces">
                             
                        </div>

                         <div class="badge-row " id="segmentBBounces">       
                        </div>
                      </div>
                      <div class="chart1 has-fixed-height" id="chart6"></div>
                    </div>

                </div>
               
            </div>
        </div>
    </div>
 
</div>
<script type="text/javascript">
        $(document).ready(function() {
                var url = $('.chart1').attr('data-url');
                
                $.ajax({url: url, 
                        success: function(result) {
                         console.log(result);
                            var obj = JSON.parse(result);
                           // console.log(JSON.parse(result));
                            console.log(obj.segmentAopenstatus);
                    //$("#h11").html(result);

      
    var options = {
        series: [{
          name: "Segment A Opens",
          data: obj.totalOpenCountA
        },
        {
          name: "Segment B Opens",
          data: obj.totalOpenCountB
        }
        ],
        chart: {
        height: 350,
        type: 'line',
        zoom: {
          enabled: false
          }
        },
        colors: ['#1f0ffa', '#fdaf06'],
        dataLabels: {
          enabled: true,
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        title: {
          text: 'Opens by Date',
          align: 'left'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 1.0
          },
        },
        xaxis: {
          categories: obj.totalOpenDatesA,
          
        }
        };
        console.log(obj.segmentAclickstatus);
        var insight = obj.insight1;
        if(insight.includes('A') == true){
          $('#segmentAopenstatus').html(obj.segmentAopenstatus);
          $('#segmentAdelivertatus').html(obj.segmentAdelivertatus);
          $('#segmentAClicks').html(obj.segmentAclickstatus);
          $('#segmentABounces').html(obj.segmentABouncesstatus);
        }else if(insight.includes('B') == true){
          $('#segmentBopenstatus').html(obj.segmentBopenstatus);        
          $('#segmentBdelivertatus').html(obj.segmentBdelivertatus);        
          $('#segmentBClicks').html(obj.segmentBclickstatus);
          $('#segmentBBounces').html(obj.segmentBBouncesstatus)
        }
        
        
        

        var chart = new ApexCharts(document.querySelector("#chart3"), options);
        chart.render();

          var options = {
          series: [{
            name: "Segment A Delivered",
            data: obj.totalDeliversCountA
        },
        {
            name: "Segment B Delivered",
            data: obj.totalDeliversCountB
        }
        ],
          chart: {
          height: 350,
          type: 'line',
          zoom: {
            enabled: false
          }
        },
         colors: ['#1f0ffa', '#fdaf06'],
        dataLabels: {
          enabled: true,
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        title: {
          text: 'Delivered by Date',
          align: 'left'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 1.0
          },
        },
        xaxis: {
          categories: obj.totalDeliversDatesA,
        }
        };

        var chart = new ApexCharts(document.querySelector("#chart4"), options);
        chart.render();
        

        var options = {
        series: [{
          name: "Segment A Clicks",
          data: obj.totalClickCountA
        },
        {
          name: "Segment B Clicks",
          data: obj.totalClickCountB
        }
        ],
        chart: {
        height: 350,
        type: 'line',
        zoom: {
          enabled: false
          }
        },
        colors: ['#1f0ffa', '#fdaf06'],
        dataLabels: {
          enabled: true,
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        title: {
          text: 'Click by Date',
          align: 'left'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 1.0
          },
        },
        xaxis: {
          categories: obj.totalClickDatesA,
          
        }
        };

        

        var chart = new ApexCharts(document.querySelector("#chart5"), options);
        chart.render();
        

      var options = {
        series: [{
          name: "Segment A Bounce",
          data: obj.totalBounceCountA
        },
        {
          name: "Segment B Bounce",
          data: obj.totalBounceCountB
        }
        ],
        chart: {
        height: 350,
        type: 'line',
        zoom: {
          enabled: false
          }
        },
        colors: ['#1f0ffa', '#fdaf06'],
        dataLabels: {
          enabled: true,
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        title: {
          text: 'Bounce by Date',
          align: 'left'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 1.0
          },
        },
        xaxis: {
          categories: obj.totalBounceDatesA,
          
        }
        };

        

        var chart = new ApexCharts(document.querySelector("#chart6"), options);
        chart.render();
      

        }});
           
    });
 
</script>
