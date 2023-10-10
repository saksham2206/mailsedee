@extends('layouts.frontend')

@section('title', trans('messages.Automations'))

@section('page_script')
    
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script> 
    <!-- <script type="text/javascript" src="{{ URL::asset('jquery-steps/build/jquery.steps.min.js') }}"></script>  -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"></link>
    <script src="{{ URL::asset('assets2/lib/automation.js') }}"></script>
        
    <!-- App -->
    <link href="{{ URL::asset('assets2/css/app.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css">
    
    <!-- Dropzone -->
    <script type="text/javascript" src="{{ URL::asset('assets2/lib/dropzone/dropzone.js') }}"></script>
    <link href="{{ URL::asset('assets2/lib/dropzone/dropzone.css') }}" rel="stylesheet" type="text/css">
    
    <!-- Ajax box -->
    <script type="text/javascript" src="{{ URL::asset('assets2/js/box.js') }}"></script>
        
    <!-- Scrollbar -->
    <script type="text/javascript" src="{{ URL::asset('assets2/lib/scrollbar/jquery.scrollbar.min.js') }}"></script>
    <link href="{{ URL::asset('assets2/lib/scrollbar/jquery.scrollbar.css') }}" rel="stylesheet" type="text/css">

    <style>
        rect.selected {
            stroke-width: 1 !important;;
            stroke-dasharray: 5;
        }

        rect.element {
            stroke:black;
            stroke-width:0;
        }

        rect.action {
            fill: rgb(101, 117, 138);
        }

        rect.trigger {
            fill: rgba(12, 12, 12, 0.49);
        }

        rect.wait {
            fill: #fafafa;
            stroke: #666;
            stroke-width: 1;
        }

        rect.operation {
            fill: #966089;
        }

        g.wait > g > a tspan {
            fill: #666;
        }

        rect.condition {
            fill: #e47a50;
        }

        g text:hover, g tspan:hover {
            fill: pink !important;
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
    </style>      
@endsection
@section('content')
    <link rel="stylesheet" href="{{url('setup-form/css/style.css')}}">

    <section class="signup-step-container">
          <div class="container">
              <div class="row d-flex justify-content-center">
                  <div class="col-md-8">
                      <div class="wizard">
                          <div class="wizard-inner">
                              <div class="connecting-line"></div>
                              <div class="connecting-line green_line"></div>
                              <ul class="nav nav-tabs" role="tablist">
                                  <li role="presentation" class="active">
                                      <a href="setup-1.html"><span class="round-tab">1 </span> <i>Step 1</i></a>
                                  </li>
                                  <li role="presentation" class="disabled">
                                      <a href="setup-2.html"><span class="round-tab">2</span> <i>Step 2</i></a>
                                  </li>
                                  <li role="presentation" class="disabled">
                                      <a href="setup-3.html"><span class="round-tab">3</span> <i>Step 3</i></a>
                                  </li>
                                 
                              </ul>
                          </div>

      </section>
    <div class="row">
        
        <div class="col-md-12">
            
            <form  action="{{ url("automation/step1Store") }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}            
                
                <h1 class="mb-20">{{ trans('messages.automation.create_automation') }}</h1>
            
                <p class="mb-10">{{ trans('messages.automation.name_your_automation') }}</p>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        @include('helpers.form_control', [
                            'type' => 'text',
                            'class' => '',
                            'label' => '',
                            'name' => 'name',
                            'value' => $automation->name,
                            'help_class' => 'automation',
                            'rules' => $automation->rules(),
                        ])
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-8">
                        
                        @include('helpers.form_control', [
                            'type' => 'select',
                            'include_blank' => 'Connect Mail Server',
                            'label' => '',
                            'class' => 'mail_server_connect',
                            'name' => 'mail_server',
                            'value' => (is_object($automation->sendingServerList) ? $automation->sendingServerList->id : ''),
                            'options' => Auth::user()->customer->getSendingServerSelectOptions(),
                            'rules' => $automation->rules(),
                            'rules' => $automation->rules(),
                        ])
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-8">
                        @include('helpers.form_control', [
                            'type' => 'text',
                            'class' => 'from_name',
                            'label' => '',
                            'name' => 'from_name',
                            'value' => "",
                            'help_class' => 'automation',
                            'rules' => $automation->rules(),
                        ])
                    </div>
                </div>
               <!--  </fieldset> -->
                <!-- <h3>First Step</h3> -->   
                
                <input type="submit" name="submit" value="Next">
                
                <!-- <div class="text-center">
                    <button class="btn btn-mc_primary mt-20">{{ trans('messages.automation.get_started') }}</button>
                </div> -->
                    
            </form>
                
        </div>
    </div>
    <!--Email Template Modal -->
    <div class="modal fade" id="EmailTemplateModel" tabindex="-1" role="dialog" aria-labelledby="EmailTemplateModel" aria-hidden="true">
      
    </div>
    <!--For Opening A popup For Conneting Server By Rajat --> 
    <script type="text/javascript">
        $(".mail_server_connect").change(function(){
            var uid = $(this).val();
            if(uid == ""){
                alert("connect Popup");
            }
        }); 
    </script>
    <!-- Wizad Script By Rajat --> 
    <script type="text/javascript">

        var form = $("#automationCreate").show();

        form.steps({
            headerTag: "h3",
            bodyTag: "fieldset",
            transitionEffect: "slideLeft",
            onStepChanging: function (event, currentIndex, newIndex)
            {
                // Allways allow previous action even if the current form is not valid!
                if (currentIndex > newIndex)
                {
                    return true;
                }
                // Forbid next action on "Warning" step if the user is to young
                if (newIndex === 3 && Number($("#age-2").val()) < 18)
                {
                    return false;
                }
                // Needed in some cases if the user went back (clean up)
                if (currentIndex < newIndex)
                {
                    // To remove error styles
                    form.find(".body:eq(" + newIndex + ") label.error").remove();
                    form.find(".body:eq(" + newIndex + ") .error").removeClass("error");
                }
                form.validate().settings.ignore = ":disabled,:hidden";
                return form.valid();
            },
            onStepChanged: function (event, currentIndex, priorIndex)
            {
                // Used to skip the "Warning" step if the user is old enough.
                if (currentIndex === 2 && Number($("#age-2").val()) >= 18)
                {

                    form.steps("next");
                }
                // Used to skip the "Warning" step if the user is old enough and wants to the previous step.
                if (currentIndex === 2 && priorIndex === 3)
                {
                    form.steps("previous");
                }
                $('#tabs li a:not(:first)').addClass('inactive');
                $('.container').hide();
                $('.container:first').show();
                    
                $('#tabs li a').click(function(){
                    var t = $(this).attr('id');
                  if($(this).hasClass('inactive')){ //this is the start of our condition 
                    $('#tabs li a').addClass('inactive');           
                    $(this).removeClass('inactive');
                    
                    $('.container').hide();
                    $('#'+ t + 'C').fadeIn('slow');
                 }
                });
            },
            onFinishing: function (event, currentIndex)
            {
                form.submit;
                return form.valid();
            },
            onFinished: function (event, currentIndex)
            {
                form.submit();
                alert("Submitted!");
            }
        }).validate({
            errorPlacement: function errorPlacement(error, element) { element.before(error); },
            rules: {
                confirm: {
                    equalTo: "#password-2"
                }
            }
        }); 
    </script>
    <!-- tabs Script Start Here By Rajat -->
    <script type="text/javascript">
        $(document).ready(function(){
            $('#tabs li a:not(:first)').addClass('inactive');
            $('.container').hide();
            $('.container:first').show();
                
            $('#tabs li a').click(function(){
                var t = $(this).attr('id');
              if($(this).hasClass('inactive')){ //this is the start of our condition 
                $('#tabs li a').addClass('inactive');           
                $(this).removeClass('inactive');
                
                $('.container').hide();
                $('#'+ t + 'C').fadeIn('slow');
             }
            });
        });
    </script>

    <!-- tabs Script End Here By Rajat -->
    <!-- Drag And Drop Script Start Here By Rajat -->
    
    <script>
      $(document).ready(function () {
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
              $('#uploaded_file').append(sendData);
              $('.HeaderColumn').append(headerData);
            }
          });
        }
      });
      function importContact(){
        var EmailField = $("#EmailField").val();
        var NameField = $("#NameField").val();
        var keyName = $("#keyName").val();
        var valueName = $("#valueName").val();
        var wholecsvdata = $("#wholecsvdata").text();
        var from_name = $(".from_name").val();
        var sending_server = $(".mail_server_connect").val();
        var token = $("input[name=_token]").val();
        $.ajax({
            url:"{{url('automation/importContacts')}}",
            method:"POST",
            data : {"EmailField":EmailField,"NameField":NameField,"keyName":keyName,"valueName":valueName,"wholecsvdata":wholecsvdata,"from_name":from_name,"sending_server":sending_server,"_token":token},
            success:function(response){
                console.log(response);

            }

        });



      }
    </script>
    <!-- Drag And Drop Script End Here By Rajat -->

    <script type="text/javascript">
        function showEmailTemplatePopup(id){
            $.ajax({
                url: "{{url('automation/createSequenceTemplate')}}",
                data:{'type':'initial','id':id},
                success:function(response){
                    $("#EmailTemplateModel").html(response);
                    $("#EmailTemplateModel").modal('show');

                }
            });
        }

        function submitTemplateForm(){
            //$("#templateForm").e.preventDefault();
            CKEDITOR.instances.content.updateElement();
            var templateFormData = $("#templateForm").serialize();
            //var token = $("input[name=_token]").val();
            console.log(templateFormData);
            $.ajax({
                url : "{{ url('automation/storeTemplate') }}",
                method:"POST",
                data: templateFormData,
                success:function(response){
                    var obj  = response;
                    if(obj.status == true){
                        var squencenumber = $("#sequence-steps_"+obj.sequenceId).val();
                        
                        var newSequence = parseInt(squencenumber) + 1;
                        if(obj.type == 'initial'){
                            uid = setId('trigger');
                            child = null; 
                        }else{
                            uid = setId();
                            child = null;
                            $("#child_"+squencenumber).val(uid);
                        }
                        $("#sequence-steps_"+obj.sequenceId).val(newSequence);
                        var html = "<div class='col-sm-6 outer-box' >";
                        html += "<input type='hidden' name='id["+obj.sequenceId+"][]' id='uid_"+newSequence+"' value='"+uid+"'>";
                        html += "<input type='hidden' name='child["+obj.sequenceId+"][]' id='child_"+newSequence+"' value='"+child+"'>";
                        html += "<input type='hidden' name='template_uid["+obj.sequenceId+"][]' id='template_uid_"+newSequence+"' value='"+obj.data.uid+"'>";
                        html += "<input type='hidden' name='type["+obj.sequenceId+"][]' id='type_"+newSequence+"' value='"+obj.type+"'>";
                        html +="<div class='title-section'>"+obj.data.subject+"</div>";
                        html +="<div class='content-section'>"+obj.data.content+" </div>";
                        html +='<button type="button" class="btn btn-success" onclick="showbtngroup('+newSequence+');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"></path></svg></button>';
                        html += '<div class="btn-group" id="btngrp_'+newSequence+'" role="group" aria-label="Basic example"><button type="button" class="btn btn-secondary" onclick="followup('+obj.sequenceId+');">Follow Up</button><button type="button" class="btn btn-secondary" onclick="addclick('+obj.sequenceId+')">click</button></div>'
                        html +="</div>";
                        $("#data_"+obj.sequenceId).append(html);

                        $("#btngrp_"+newSequence).hide();
                        
                        $('#create_sequence_btn').css('display', 'none');
                        $("#EmailTemplateModel").modal('hide');

                    }
                }
            });
        }

        function showbtngroup(id){
            $("#btngrp_"+id).show();
        }

        function followup(id){
            $.ajax({
                url: "{{url('automation/createSequenceTemplate')}}",
                data:{'type':'followup','id':id},
                success:function(response){
                    $("#EmailTemplateModel").html(response);
                    $("#EmailTemplateModel").modal('show');
                    $("#btngrp_"+id).hide();

                }
            });
            
        }
        function addclick(id){
            $.ajax({
                url: "{{url('automation/createSequenceTemplate')}}",
                data:{'type':'click','id':id},
                success:function(response){
                    $("#EmailTemplateModel").html(response);
                    $("#EmailTemplateModel").modal('show');
                    $("#btngrp_"+id).hide();

                }
            });
            
        }
        function setId(id=null) {
            alert(id);
        if (id == null) {
            var randomId = Math.floor(Math.random() * 999999999) + 100000000;
            id = randomId;
        } else {
            id = id;
        }
        return id;
    }

    function AddSegment(){
        
        $(".segment").clone().appendTo('.mainDiv');
        $(".mainDiv .segment").removeClass('hide');
        var segmentNumber = $("#segmentNumber").val();
        $(".mainDiv .segment").attr('id','segment_'+(parseInt(segmentNumber)+1));
        $(".mainDiv .segment").addClass('col-sm-6');
        var funcNmae = "showEmailTemplatePopup("+(parseInt(segmentNumber)+1)+")";
        $(".mainDiv .segment > .data").attr('id','data_'+(parseInt(segmentNumber)+1));
        $(".mainDiv .segment").append('<input type="hidden" name="sequence-steps" class="sequence-steps" id="sequence-steps_'+(parseInt(segmentNumber)+1)+'" value="0">');
        $(".mainDiv .segment > .create_sequence_btn").attr('onclick',funcNmae);
        $(".mainDiv .segment").removeClass('segment');
        $("#segmentNumber").val(parseInt(segmentNumber)+1);
    }
    </script>

    

@endsection