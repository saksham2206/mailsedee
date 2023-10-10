@extends('layouts.automation.main')

@section('title', trans('messages.automation.create'))

@section('page_script')
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"></link>
            <link href="https://sende.testmywebsite.in/assetsnew/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="https://cdn.tiny.cloud/1/rnb832fakcjvuxhuesi1fsl4trkhfl30f8fz5yewgyjje1ik/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <style>
       
#overlayer {
    width: 100%;
    position: fixed;
    z-index: 1;
    text-align: center;
    opacity: 0.6;
    background: #4a4a4a;
    height: 100vh;
}
.loader {
  display: inline-block;
  width: 30px;
  height: 30px;
  position: absolute;
  z-index:3;
  border: 4px solid #Fff;
  top: 50%;
  animation: loader 2s infinite ease;
}

.loader-inner {
  vertical-align: top;
  display: inline-block;
  width: 100%;
  background-color: #fff;
  animation: loader-inner 2s infinite ease-in;
}

@keyframes loader {
  0% {
    transform: rotate(0deg);
  }
  
  25% {
    transform: rotate(180deg);
  }
  
  50% {
    transform: rotate(180deg);
  }
  
  75% {
    transform: rotate(360deg);
  }
  
  100% {
    transform: rotate(360deg);
  }
}

@keyframes loader-inner {
  0% {
    height: 0%;
  }
  
  25% {
    height: 0%;
  }
  
  50% {
    height: 100%;
  }
  
  75% {
    height: 100%;
  }
  
  100% {
    height: 0%;
  }
}

        .automation2 .diagram {
    background: #ffffff !important;
}
        rect.selected {
            stroke-width: 1 !important;;
            stroke-dasharray: 5;
        }

        rect.element {
            stroke:black;
            stroke-width:0;
        }
rect.action {
    fill: rgb(255 255 255) !important;
    stroke-width: 2px !important;
    stroke: black !important;
}

        rect.trigger {
            fill: rgba(12, 12, 12, 0.49);
        }

        rect.wait {
            fill: #ffa500 !important;
            stroke: #000 !important;
            stroke-width: 2 !important;
        }

        rect.operation {
            fill: #966089;
        }

        g.wait > g > a tspan {
            fill: #666;
        }

        rect.condition {
            fill: blue !important;
            stroke-width: 1 !important;
        }
         g.wait > g > a tspan {
            fill: #fff !important;
        }
        g text:hover, g tspan:hover {
            fill: pink !important;
        }
        .maping_data {
   padding-top: 20px;
   }
   .maping_data .row {
   display: inline-block !important;
   margin-bottom: 20px;
   }
   .maping_data .row select {
   background: #fff;
   border: none;
   color: #4a2ef0;
   font-weight: bolder;
   font-size: 13px;
   }
   .maping_data .row label {
   font-size: 13px;
   }
   .modal-backdrop
   {
   opacity:0.5 !important;
   }
   .maping_data .row {
    display: block !important;
}
.wizard > div.wizard-inner {
    position: relative;
    margin-bottom: 50px;
    text-align: right !important;
}
.wizard .nav-tabs > li:first-child {
    width: 14% !important;
}
.wizard .nav-tabs > li {
    width: 70% !important;
}
    </style>
    <style>
      h3 {
        line-height: 30px;
        text-align: center;
      }
     
      #drop_file_area {
        height: 200px;
        border: 2px dashed #ccc;
        line-height: 200px;
        text-align: center;
        font-size: 20px;
        background: #f9f9f9;
        margin-bottom: 15px;
      }
     
      .drag_over {
        color: #000;
        border-color: #000;
      }
     
      .thumbnail {
        width: 100px;
        height: 100px;
        padding: 2px;
        margin: 2px;
        border: 2px solid lightgray;
        border-radius: 3px;
        float: left;
      }
     
      #upload_file {
        display: none;
      }

      .page-content {
            display: block;
        }
        .content-wrapper {
            display: block;
            vertical-align: top;
        }
        .connecting-line {
    top: 14px !important;
}
.wizard .nav-tabs > li a i {
    transform: translate(-4%, -50%) !important;
}
.green_line {
    width: 70% !important;
}
    </style>
    <style type="text/css">
        .outer-box{
            
            height: 150px;
            border: 1px solid blue;
            padding: 0px !important;
        }
        .title-section{
            width: 100%;
            height: 50px;
            border: 1px solid blue;
            padding: 0px !important;
        }
        .content-section{
             width: 100%;
            height: 100px;
            border: 1px solid blue;
            padding: 0px !important;
        }
        .swal-title {
  display: block !important;
}
.btn_top {
    margin-top: 30px;
}
a.btn.btn-primary.btn_top:hover {
    background: #1f0ffa;
}
    </style>      
@endsection
@section('content')
    <div id="overlayer" style="display:none;">
        <span class="loader" style="display:none">
  <span class="loader-inner"></span>
</span>
    </div>

    
    <link rel="stylesheet" href="{{url('setup-form/css/style.css')}}">
    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
            <a class="navbar-brand left-logo" href="{{url('/')}}">
                @if (\Acelle\Model\Setting::get('site_logo_small'))
                    <img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
                @else
                    <img height="22" src="{{ URL::asset('images/logo_light_blue.svg') }}" alt="">
                @endif
            </a>
            <div class="d-inline-block d-flex mr-auto align-items-center"  style="font-size: .84rem;">
                <i class="material-icons-outlined automation-head-icon ml-2">alarm</i>
                {{ $automation->name }}
                
            </div>
            <div class="automation-top-menu">
               
                <span class="mr-3"><i class="last_save_time" data-url="{{ action('Automation2Controller@lastSaved', $automation->uid) }}">{{ trans('messages.automation.designer.last_saved', ['time' => $automation->updated_at->diffForHumans()]) }}</i></span>
                <a href="{{ action('Automation2Controller@index') }}" class="action">
                    <i class="material-icons-outlined mr-2">arrow_back</i>
                    {{ trans('messages.automation.go_back') }}
                </a>

                <div class="switch-automation d-flex">
                    <select class="select select2 top-menu-select" name="switch_automation">
                        <option value="--hidden--"></option>
                        @foreach($automation->getSwitchAutomations(Auth::user()->customer)->get() as $auto)
                            <option value='{{ action('Automation2Controller@edit', $auto->uid) }}'>{{ $auto->name }}</option>
                        @endforeach
                    </select>

                    <a href="javascript:'" class="action">
                        <i class="material-icons-outlined mr-2">
                        horizontal_split
                        </i>
                        {{ trans('messages.automation.switch_automation') }}
                    </a>
                </div>

<div class="account-info">
                    <ul class="navbar-nav mr-auto navbar-dark bg-dark">                        
                        <li class="nav-item dropdown">
                            <a class="account-item nav-link dropdown-toggle px-2 pro-user-name ms-1" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="avatar" src="{{ Auth::user()->getProfileImageUrl() }}" alt="">
                                {{ Auth::user()->displayName() }}
                                <i class="mdi mdi-chevron-down"></i>
                            </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    @can("admin_access", Auth::user())
                        <a href="{{ action("Admin\HomeController@index") }}" class="dropdown-item notify-item">
                            <i class="fe-user"></i>
                            {{ trans('messages.admin_view') }}</a>
                              <!-- <div class="dropdown-divider"></div> -->
                        @endif
                       <!--  @if (request()->user()->customer->activeSubscription())
                                
                                <a href="#" class="dropdown-item notify-item" data-url="{{ action("AccountController@quotaLog") }}">
                                <i class="fe-user"></i>
                                    <span class="">{{ trans('messages.used_quota') }}</span>
                                </a>
                        
                        @endif -->
                    <!-- item-->
                    <a href="{{ action('AccountSubscriptionController@index') }}" class="dropdown-item notify-item">
                        <i class="fa fa-hand-pointer-o" aria-hidden="true"></i>
                        <span>{{ trans('messages.subscriptions') }}</span>
                        @if (Auth::user()->customer->hasSubscriptionNotice())
                                    <i class="material-icons-outlined subscription-warning-icon text-danger">info</i>
                                @endif
                    </a>
                      <!-- item-->
                    <a href="{{ action("AccountController@billing") }}" class="dropdown-item notify-item">
                       <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        <span>{{ trans('messages.billing') }}</span>
                    </a>
    
                    <!-- item-->
                    <a href="{{ action("AccountController@profile") }}" class="dropdown-item notify-item">
                        <i class="fe-settings"></i>
                        <span>{{ trans('messages.account') }}</span>
                    </a>
    
                    <!-- item-->
                  <!--   <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock"></i>
                        <span>Lock Screen</span>
                    </a> -->
                        <!-- @if (Auth::user()->customer->canUseApi())
                                <a href="{{ action("AccountController@api") }}" class="dropdown-item notify-item">
                                <i class="fe-lock"></i>
                                <span>{{ trans('messages.campaign_api') }}</span>
                                </a>
                        
                        @endif -->
                    <!-- <div class="dropdown-divider"></div> -->
    
                    <!-- item-->
                    <a href="{{ url('/logout') }}" class="dropdown-item notify-item">
                        <i class="fe-log-out"></i>
                        <span>Logout</span>
                    </a>
                  </div>
                        </li>
                    </ul>
                    
                </div>
            </div>
        </nav>
    </header>
    <section class="signup-step-container">
          <div class="container">
              <div class="row d-flex justify-content-center">
                  <div class="col-md-8">
                      <div class="wizard">
                          <div class="wizard-inner">
                              <div class="connecting-line"></div>
                              <div class="connecting-line green_line" style="width: 30%;"></div>
                              <ul class="nav nav-tabs" role="tablist">
                                  <li role="presentation" class="active">
                                      <a href="javascript:void(0);"><span class="round-tab">1 </span> <i>Step 1</i></a>
                                  </li>
                                  <li role="presentation" class="active">
                                      <a href="javascript:void(0);"><span class="round-tab">2</span> <i>Step 2</i></a>
                                  </li>
                                  
                                  <!-- <li role="presentation" class="disabled">
                                      <a href="setup-4.html"><span class="round-tab">4</span> <i>Step 4</i></a>
                                  </li> -->
                              </ul>
                          </div>

      </section>
      <main role="main">
        <div class="automation2">

            
        </div>
    </main>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8 mb-2">
            <form action="{{ url("automation/step3store") }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}
                <!-- <h3>Contact</h3>
                <fieldset> -->
                <div>
                <p>Add your subscribers to begin your campaign. For reference, download our <a href='{{url("/")}}/contacts.csv'>sample format</a>.</p>
                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs mt-3 mb-4 upload_contact" role="tablist" id="tabdata">
                    <li role="presentation" class="nav-item active"><a href="#home" aria-controls="home" class="nav-link" role="tab" data-toggle="tab">Upload CSV</a></li>
                    <li role="presentation" class="nav-item"><a href="#profile" class="nav-link" aria-controls="profile" role="tab" data-toggle="tab">Manually Create </a></li>
                    <li role="presentation"><a href="#messages" class="nav-link" aria-controls="messages" role="tab" data-toggle="tab">Existing List</a></li>
                    
                  </ul>

                  <!-- Tab panes -->
                  <div class="tab-content">
                    <input type="file" name="files[]" id="contactFile" style="display: none;">
                    <div role="tabpanel" class="tab-pane active" id="home">
                        <div id="drop_file_area" onclick="openContact();" style="cursor: pointer;">
                          Drag and Drop Files Here or Click here to select file
                          
                        </div>
                        <div id="uploaded_file" style="max-height: 300px; overflow:scroll; display: none;"></div>
                        <textarea id="wholecsvdata" style="display:none;"></textarea>

                        <div class="maping_data">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Email Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="EmailField" id="EmailField">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Name Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="NameField" id="NameField">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="row">
                                <div class="col-sm-12">
                                    <label>Other Field Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="keyName" id="keyName">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                    <select class="HeaderColumn" name="valueName" id="valueName">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div> -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Company Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="CompanyField" id="CompanyField">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div>                            
                        </div>
                         <div class="row">
                            <div class="col-sm-12">
                                <label>Check Email Validation</label>

                                <input type="checkbox" name="Email_Validate" id="Email_Validate" value="1">
                                <input id='testNameHidden' type='hidden' value='0' name='testName'>
                            </div>
                        </div>
                        <a class="btn btn-primary btn_top" id="importContact" onclick="importContact();" style="display:none;">Import</a>
                       

                    </div>
                    <div role="tabpanel" class="tab-pane" id="profile">
                        <div class="ms-hint"> <i class="fa fa-question-circle" style="margin-right: 5px;"></i><span class="inner"> To include names with your email addresses enter them like so: <br><pre>"Jane Doe" &lt;jane@doe.com&gt; [company name]</pre></span></div>
                        <!-- <input type="text" name="list_name" id="list_name" class="form-control"> -->
                        <textarea name="list_data" id="list_data1" class="form-control"></textarea>
                        <div class="row" style="padding-top:15px ;">
                            <div class="col-sm-12">
                                <label>Check Email Validation</label>

                                <input type="checkbox" name="Email_Validate" id="Email_Validate" value="1">
                                <input id='testNameHidden' type='hidden' value='0' name='testName'>
                            </div>
                        </div>
                        <a class="btn btn-success mt-2" id="importContactManualy" onclick="importContactManualy();">Import</a>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="messages">
                        <div class="row">
                            <div class="col-md-6">
                                @include('helpers.form_control', [
                                    'name' => 'mail_list_uid',
                                    'include_blank' => trans('messages.automation.choose_list'),
                                    'class' => 'mail_list_box mail_list_uid',
                                    'type' => 'select',
                                    'label' => '',
                                    'value' => (is_object($automation->mailList) ? $automation->mailList->uid : ''),
                                    'options' => Auth::user()->customer->readCache('MailListSelectOptions', []),
                                    'rules' => $automation->rules(),
                                ])
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="list_id" value="{{$automation->mail_list_id}}">
                    <input type="hidden" name="automation_uid" value="{{$automation->uid}}">
                    <input type="hidden" name="contactType" value="CSV" id="contactType">
                  </div>

                </div>
                
                 
                <!-- </fieldset> -->
            </form>
                
    <div class="row">
        <div class="col-md-12">
            
                <a href="{{url('automation/create')}}/{{$AutomationList->uid}}" class="btn btn-primary btn_top" style="float:left;">Previous</a>
                <a href="{{url('automation/finishStep')}}/{{$AutomationList->uid}}" class="btn btn-primary btn_top" style="float:right;" > Finish</a>
            
        </div>
    </div>
        </div>
        <div class="col-md-2"></div>
    </div>


    <!-- tabs Script Start Here By Rajat -->
    <script type="text/javascript">

        // $(document).ready(function(){
            
        //     $('#tabdata li a:not(:first)').addClass('inactive');
        //     $('.container').hide();
        //     $('.container:first').show();
                
        //     $('#tabdata li a').click(function(){
        //         var t = $(this).attr('id');
        //       if($(this).hasClass('inactive')){ //this is the start of our condition 

        //         $('#tabdata li a').addClass('inactive');
        //         $(this).addClass('active');           
        //         $(this).removeClass('inactive');
                
        //         $('.container').hide();
        //         $('#'+ t + 'C').fadeIn('slow');
        //      }
        //     });
        // });
    </script>

    <!-- tabs Script End Here By Rajat -->
    <!-- Drag And Drop Script Start Here By Rajat -->
    
    <script>
      $(document).ready(function () {
        
        $(".HeaderColumn").change(function(){
            var btntext = "false";
            $(".HeaderColumn").each(function(){
                if($(this).val() == ''){
                    btntext = "true";
                }
            });
            if(btntext != "true"){
                $("#importContact").show();
            }
        });
        $(".maping_data").hide();
        $(".mail_list_box").change(function(){
            $("#contactType").val('select');
        });
        $("html").on("dragover", function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
     
        $("html").on("drop", function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
     
        $('#drop_file_area').on('dragover', function () {
          $(this).addClass('drag_over');
          return false;
        });
     
        $('#drop_file_area').on('dragleave', function () {
          $(this).removeClass('drag_over');
          return false;
        });
     
        $('#drop_file_area').on('drop', function (e) {
          e.preventDefault();
          $(this).removeClass('drag_over');
          var formData = new FormData();
          var files = e.originalEvent.dataTransfer.files;
          for (var i = 0; i < files.length; i++) {
            formData.append('file[]', files[i]);
          }
          var token = $("input[name=_token]").val();
          formData.append("_token",token)
          uploadFormData(formData);
        });
        

        $('#contactFile').change(function(){
            var formData = new FormData();
                var files = $('#contactFile')[0].files;
             // var files = $("input[name=files[]]").val();
              for (var i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]);
              }
              var token = $("input[name=_token]").val();
              formData.append("_token",token)
              uploadFormData(formData);
        });
     
        function uploadFormData(form_data) {
            var token = $("input[name=_token]").val();
          $.ajax({
            url: "{{url('automation/uploadCsv')}}",
            method: "POST",
            data: form_data,
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                var obj = JSON.parse(data);
                console.log(obj);
                if(obj.status == false){
                    swal.fire(obj.Msg);
                }else{
                    var sendData ="<table class='table table-stripe table-bordered'>";
                    var headerData;
                  //var files = e.originalEvent.dataTransfer.files;
                  for(var i = 0; i < obj.length; i++){
                    sendData += "<tr>";

                    for(var l= 0; l < obj[i]['header'].length; l++){
                        sendData += "<th>"+obj[i]['header'][l]+"</th>";
                        headerData +="<option value='"+obj[i]['header'][l]+"'>"+obj[i]['header'][l]+"</option>";
                    }
                    sendData += "</tr>";
                    var finalData = obj[i]['data'];
                    for (var j = 0; j < finalData.length; j++) {
                        //console.log(finalData);
                        sendData += "<tr>";
                        for (var k = 0; k < obj[i]['header'].length ; k++) {
                            var header = obj[i]['header'][k];
                            //headerArray = obj[i]['header']; 
                            sendData +="<td>"+finalData[j][header]+"</td>"; 
                            
                        }
                        sendData += "</tr>";
                    }
                }
                sendData += "</tr>";
              $("#wholecsvdata").text(data);
              $('#uploaded_file').css('display','block');
              $('#uploaded_file').append(sendData);
              $(".maping_data").show();
              $("#contactType").val('CSV');
              $('.HeaderColumn').append(headerData);
              

                
              }  
              
            }
          });
        }
      });
      function openContact(){
            $("#contactFile").trigger('click');
        }
      function importContact(){
        $('.loader').show();
        $("#overlayer").show();
        var EmailField = $("#EmailField").val();
        var NameField = $("#NameField").val();
        var keyName = $("#keyName").val();
        var valueName = $("#valueName").val();
        var CompanyField = $("#CompanyField").val();
        var wholecsvdata = $("#wholecsvdata").text();
        var from_name = $(".from_name").val();
        var sending_server = $(".mail_server_connect").val();
        var token = $("input[name=_token]").val();
        var list_id = $("input[name=list_id]").val();
        if(document.getElementById("Email_Validate").checked) {
            var Email_Validate = $("input[name=Email_Validate]").val();
            
        }else{
           var Email_Validate = $("input[name=testName]").val(); 
        }
        $.ajax({
            url:"{{url('automation/importContacts')}}",
            method:"POST",
            data : {"EmailField":EmailField,"NameField":NameField,"keyName":keyName,"valueName":valueName,"wholecsvdata":wholecsvdata,'list_id':list_id,"from_name":from_name,"sending_server":sending_server,"_token":token,'CompanyField':CompanyField,"Email_Validate":Email_Validate},
            success:function(response){
                if(response.status == false){
                    swal.fire('Remove all invalid email ids and try import again !');
                }else{
                    var msgText = ''
                if(response.DuplicateCount >0 || response.BlacklistCount >0 ||response.BlacklistCount>0 ||response.notVerifyCount>0){
                    msgText += '<ul>';
                    if(response.DuplicateCount >0){
                        msgText += "<li>"+response.DuplicateCount+" duplicate records skipped.</li>"
                    }
                    if(response.BlacklistCount >0){
                        msgText += "<li>"+response.BlacklistCount+" Nondelivereble receipients skipped.</li>"
                    }
                    if(response.notVerifyCount > 0){
                        msgText += "<li>"+response.notVerifyCount+" Not Verified receipients skipped.</li>"
                    }
                   msgText +='</ul>';
                }
                const wrapper = document.createElement('div');
                wrapper.innerHTML = msgText
                msgText +='</div>';
                console.log(response);
                //if(msgText  ){
                    swal.fire({
                    //html:true,
                    title: "Your subscriber have been successfully imported.",
                    content: wrapper,
                    confirmButtonColor: "#00695C",
                    type: "success",
                    allowOutsideClick: true,
                    confirmButtonText: "Ok",
                    customClass: "swl-success"
                });
                $('.loader').hide();
                $("#overlayer").hide();
                $('.swal2-content').html(msgText);
                // }else{
                //     swal.fire({
                //         title: "Your subscriber have been successfully imported.",
                //         text: "",
                //         confirmButtonColor: "#00695C",
                //         type: "success",
                //         allowOutsideClick: true,
                //         confirmButtonText: "Ok",
                //         customClass: "swl-success"
                //     });
                //}
                

                }
            }

        });



      }

      function importContactManualy(){
        $('.loader').show();
        $("#overlayer").show();
        var EmailField = "EMAIL";
        var NameField = "FIRST_NAME";
        var LastName = "LAST_NAME";
        var Company = 'COMPANY';
        var wholecsvdata = $("#list_data1").val();
        var wholecsvdatas = wholecsvdata.replace(/</g,'=');
        var wholecsvdatass = wholecsvdatas.replace(/>/g,'');

        //var wholecsvdatasss = wholecsvdatass.replace()
        //alert(wholecsvdata);
        $("#contactType").val('INPUT');
        var from_name = $(".from_name").val();
        var sending_server = $(".mail_server_connect").val();
        var token = $("input[name=_token]").val();
        var list_id = $("input[name=list_id]").val();
        
        if(document.getElementById("Email_Validate").checked) {
            var Email_Validate = $("input[name=Email_Validate]").val();
            
        }else{
           var Email_Validate = $("input[name=testName]").val(); 
        }
        $.ajax({
            url:"{{url('automation/importContactManualy')}}",
            method:"POST",
            data : {"EmailField":EmailField,"NameField":NameField,"LastName":LastName,"wholecsvdata":wholecsvdatass,'list_id':list_id,"from_name":from_name,"sending_server":sending_server,"_token":token,'Company':Company,"Email_Validate":Email_Validate},
            success:function(response){
                var msgText = ''
                if(response.DuplicateCount >0 || response.BlacklistCount >0 || response.notVerifyCount > 0){
                    msgText = '<ul>';
                    if(response.DuplicateCount >0){
                        msgText += "<li>"+response.DuplicateCount+" duplicate records skipped.</li>"
                    }
                    if(response.BlacklistCount >0){
                        msgText += "<li>"+response.BlacklistCount+" Nondelivereble receipients skipped.</li>"
                    }
                    if(response.notVerifyCount > 0){
                        msgText += "<li>"+response.notVerifyCount+" Not Verified receipients skipped.</li>"
                    }
                }
                const wrapper = document.createElement('div');
                wrapper.innerHTML = msgText
                msgText +='</div>';
                console.log(response);
                //if(response.count >0 ){
                    swal.fire({
                    //html:true,
                    title: "Your subscriber have been successfully imported.",
                    content: wrapper,
                    confirmButtonColor: "#00695C",
                    type: "success",
                    allowOutsideClick: true,
                    confirmButtonText: "Ok",
                    customClass: "swl-success"
                });
                $('.loader').hide();
                $("#overlayer").hide();
                $('.swal2-content').html(msgText);
                // }else{
                //     swal.fire({
                //         title: "Your subscriber have been successfully imported.",
                //         text: "",
                //         confirmButtonColor: "#00695C",
                //         type: "success",
                //         allowOutsideClick: true,
                //         confirmButtonText: "Ok",
                //         customClass: "swl-success"
                //     });
                // }

            }

        });



      }
    </script>
    <!-- Drag And Drop Script End Here By Rajat --> 

    <script type="text/javascript">
        $(document).ready(function(){

            $('.mail_list_uid').change(function(){
                var mailListId = $(this).val();
                var AuomationUid = '{{$AutomationList->uid}}';
                $.ajax({
                    url:"{{url('automation/MailListUpdate')}}/"+mailListId+'/'+AuomationUid,
                    success:function(){
                        console.log('hello');
                    }
                })
            })
        });
    </script>
    

@endsection