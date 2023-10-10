@extends('layouts.frontend')

@section('title', trans('messages.Automations'))

@section('page_script')
    
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script> 
    <script type="text/javascript" src="{{ URL::asset('jquery-steps/build/jquery.steps.min.js') }}"></script> 
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
    
    <style type="text/css">
.form-section {
    padding: 100px;
    background: #f3f5f6;
}
.form-section .form-group {
    border: 2px solid #3e7fda;
}
.form-section textarea.form-control {
    height: auto;
    border: none !important;
}
.form-section .fav-mail span {
    font-weight: 500;
    padding-left: 5px;
}
    .form-section .text-info {
    padding: 10px 16px;
    background-color: #3e7fda;
    color: #fff !important;
    font-size: 20px;
}
.form-section button.btn.btn-edit.add{
    color: #3e7fda !important;
}
.form-section button.btn.btn-edit {
    float: right;
    border: 1px solid #c5c1c1;
    padding: 5px 20px;
    text-transform: uppercase;
    font-weight: 500;
    color: #b9abab;
    font-size: 17px;
    margin-top: 3px;
    margin-right: 3px;
}
.footer-sec h3 {
    font-size: 16px;
    color: #5e5353;
}
.footer-sec {
    border-top: 1px solid #3e7fda;
    padding: 6px 12px 1px;
}
.form-control:focus {
    border-color: #ffffff !important;
    box-shadow: 0 0 0 0.2rem rgb(0 123 255 / 0%) !important;
}
.form-section .form-group {
    border: 2px solid #3e7fda;
}
.form-section .form-group {
    border: 2px solid #3e7fda;
    margin: 0px;
    background: #fff;
    position: relative;
}
.form-section .comment p{
    color: #a19696;
    padding: 16px;
}
.form-section .border-section:after {
    height: 70px;
    display: block;
    width: 2px;
    background: #3e7fda;
    border-right: 1px white;
    content: '';
    text-align: center;
    position: absolute;
    bottom: -70px;
    margin: 0px auto;
    left: 0;
    right: 0;
}

.form-section .border-clock {
    position: absolute;
    text-align: center;
    margin: 0px auto;
    border: 2px solid #a19696;
    display: inline-block;
    padding: 2px 9px;
    transform: translate(-50%, -50%);
    top: 98%;
    left: 50%;
    background: #fff;
    z-index: 99999;
}
.form-section .border-clock h3 {
    font-size: 14px;
    color: #a19696;
}
.form-section .border-clock .fa-clock{
    font-size: 16px;
    color: #a19696;
}
h2.heading-top {
    text-align: center;
    margin: 0px auto;
    text-transform: uppercase;
    color: #a19696;
    font-size: 25px;
    margin-bottom: 30px;
}
.margin-bottom {
    margin-bottom: 66px !important;
}
.margin-top{
  margin-top: 90px !important;
}
.form-section .border-plus {
    position: absolute;
    margin: 0px auto;
    display: inline-block;
    padding: 0px 10px 4px;
    transform: translate(-50%, -50%);
    top: 90%;
    left: 50%;
    background: #ff4281;
    border-radius: 4px;
}
.form-section .border-plus .fa-plus, 
.form-section .border-plus-2 .fa-plus {
    font-size: 12px;
    color: #fff;
}
.form-section .border-plus-2 {
    position: absolute;
    margin: 0px auto;
    display: inline-block;
    padding: 0px 10px 4px;
    transform: translate(-50%, -50%);
    top: 116%;
    left: 50%;
    background: #ff4281;
    border-radius: 4px;
}
.border-one{
    border-right: 3px dotted #a7a2a2;
}

.form-section .border-plus i {
    margin-right: 0;
}

.btn_center button {
    float: none !important;
}
.btn_center {
    text-align: center;
    width: 100%;
    margin: 0px auto;
}

</style>
        <section class="signup-step-container">
          <div class="container">
              <div class="row d-flex justify-content-center">
                  <div class="col-md-8">
                      <div class="wizard">
                          <div class="wizard-inner">
                              <div class="connecting-line"></div>
                              <div class="connecting-line green_line" style="width: 55%;"></div>
                              <ul class="nav nav-tabs" role="tablist">
                                  <li role="presentation" class="active">
                                      <a href="setup-1.html"><span class="round-tab">1 </span> <i>Step 1</i></a>
                                  </li>
                                  <li role="presentation" class="active">
                                      <a href="setup-2.html"><span class="round-tab">2</span> <i>Step 2</i></a>
                                  </li>
                                  <li role="presentation" class="active">
                                      <a href="setup-3.html"><span class="round-tab">3</span> <i>Step 3</i></a>
                                  </li>
                                  <li role="presentation" class="disabled">
                                      <a href="setup-4.html"><span class="round-tab">4</span> <i>Step 4</i></a>
                                  </li>
                              </ul>
                          </div>

      </section>
    <div class="row">
        
        <div class="col-md-12">
            <form action="{{ url("automation/store") }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}
                <!-- <h3>Sequence</h3>
                <fieldset> -->
               
                <section class="form-section">
                    <div class="container-fluid">
                        <div class="row">
                             <div class="mainDiv">
                            <div class="col-md-6 col-sm-12 border-one">
                                <div class="row">
                                    
                                        <div id="segment_1">
                                            <input type="hidden" name="segmentNumber" id="segmentNumber" value="1">
                                            

                                            <a href="javascript:void(0);"  class="create_sequence_btn create_sequence_btn1 " id="create_sequence_btn" onclick="showEmailTemplatePopup(1)">Create Sequence</a>
                                                <div class="data" id="data_1" >
                                                    <h2 class="heading-top">segment 1</h2>
                                                </div>
                                            
                                            <input type="hidden" name="sequence-steps" id="sequence-steps_1" value="0">
                                            

                                        </div>
                                        
                                       
                                    
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="abtest" onclick="AddSegment();">A/B Testing</a>
                            </div>
                            <div class="hide segment" id="segment">
                                
                            
                                        <div class="col-md-6 col-sm-12 ">
                                            <div class="row">                   
                                                <a href="javascript:void(0);"  class="create_sequence_btn " id="create_sequence_btn">Create Sequence</a>
                                                <div class="data" >
                                                    
                                                </div>
                                            </div>
                                        </div>
                                   
                            </div>
                        </div>
                    </div>
                </section>
                 
                
                    <input type="hidden" name="automation_uid" value="{{$automation_uid}}">
                    <input type="submit" name="submit" value="Finish" class="btn btn-success">
                    <!-- </fieldset> -->
            </form>
                
        </div>
    </div>
    <!--Email Template Modal -->
    <div class="modal fade" id="EmailTemplateModel" tabindex="-1" role="dialog" aria-labelledby="EmailTemplateModel" aria-hidden="true">
      
    </div>

<script type="text/javascript">

        var form = $("#automationCreate").show();

        
    </script>
    <!-- tabs Script Start Here By Rajat -->
    

    <!-- tabs Script End Here By Rajat -->
    <!-- Drag And Drop Script Start Here By Rajat -->
    
    
    <!-- Drag And Drop Script End Here By Rajat -->

    <script type="text/javascript">
        function showEmailTemplatePopup(id){
            $.ajax({
                url: "{{url('automation/createSequenceTemplate')}}",
                data:{'type':'initial','id':id},
                success:function(response){
                    $("#EmailTemplateModel").html(response);
                    $("#EmailTemplateModel").modal('show');
                    $("#EmailTemplateModel").removeClass('fade');

                }
            });
        }

        function submitTemplateForm(){
            //$("#templateForm").e.preventDefault();
            CKEDITOR.instances.content.updateElement();
            var TempID = $("#templateSelect").val();
            var url ='';
            if(TempID != ''){
                url = "{{ url('automation/storeTemplate') }}";
            }else{
                url = "{{ url('automation/getTempalte') }}"
            }

            var templateFormData = $("#templateForm").serialize();
            //var token = $("input[name=_token]").val();
            console.log(templateFormData);
            $.ajax({
                url : url,
                method:"POST",
                data: templateFormData,
                success:function(response){
                    var obj  = response;
                    if(obj.status == true){
                        var squencenumber = $("#sequence-steps_"+obj.sequenceId).val();
                        
                        var newSequence = parseInt(squencenumber) + 1;
                        if(obj.type == 'initial'){
                            uid = setId();
                            child = null; 
                        }else{
                            uid = setId();
                            child = null;
                            $("#child_"+squencenumber).val(uid);
                        }
                        $("#sequence-steps_"+obj.sequenceId).val(newSequence);
                        
                        var html = '<div class="col-md-12">'
                             html += '<div class="form-group margin-bottom"><div class="fav-mail"><i class="fa fa-envelope text-info"></i><span>'+obj.data.subject+'</span><button type="button" class="btn btn-edit">Edit</button><div class="comment"><p>'+obj.data.content+'</p></div></div><div class="footer-sec"><h3>1 REMAINING</h3></div><div class="border-section"></div></div><div class="border-plus"><i class="fa fa-plus" onclick="showbtngroup('+obj.sequenceId+newSequence+');"></i></div>';
                        
                        html += "<input type='hidden' name='id["+obj.sequenceId+"][]' id='uid_"+newSequence+"' value='"+uid+"'>";
                        html += "<input type='hidden' name='child["+obj.sequenceId+"][]' id='child_"+newSequence+"' value='"+child+"'>";
                        html += "<input type='hidden' name='template_uid["+obj.sequenceId+"][]' id='template_uid_"+newSequence+"' value='"+obj.data.uid+"'>";
                        
                        html += "<input type='hidden' name='type["+obj.sequenceId+"][]' id='type_"+newSequence+"' value='"+obj.type+"'>";
                        //html +="<div class='title-section'>"+obj.data.subject+"</div>";
                        //html +="<div class='content-section'>"+obj.data.content+" </div>";
                        //html +='<button type="button" class="btn btn-success" onclick="showbtngroup('+newSequence+');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"></path></svg></button>';
                        html += '<div class="btn-group btn_center" id="btngrp_'+obj.sequenceId+newSequence+'" role="group" aria-label="Basic example"><button type="button" class="btn btn-secondary" onclick="followup('+obj.sequenceId+');">Follow Up</button><button type="button" class="btn btn-secondary" onclick="addclick('+obj.sequenceId+')">click</button></div>'
                        html +="</div>";
                        var oldId = newSequence-1
                        $("#btngrp_"+obj.sequenceId+oldId).remove();
                        $("#data_"+obj.sequenceId).append(html);

                        $("#btngrp_"+obj.sequenceId+newSequence).hide();
                        
                        $('.create_sequence_btn'+obj.sequenceId).css('display', 'none');
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
            //alert(id);
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
        //$(".mainDiv .segment").addClass('col-sm-12');
        var funcNmae = "showEmailTemplatePopup("+(parseInt(segmentNumber)+1)+")";
        $(".mainDiv .segment .col-sm-12 .row > .data").attr('id','data_'+(parseInt(segmentNumber)+1));
        
        $("#data_"+(parseInt(segmentNumber)+1)).html('<h2 class="heading-top">segment 2</h2>')
        $(".mainDiv .segment").append('<input type="hidden" name="sequence-steps" class="sequence-steps" id="sequence-steps_'+(parseInt(segmentNumber)+1)+'" value="0">');
        $(".mainDiv .segment .col-sm-12 .row > .create_sequence_btn").attr('onclick',funcNmae);
        $(".mainDiv .segment .col-sm-12 .row > .create_sequence_btn").addClass('create_sequence_btn'+(parseInt(segmentNumber)+1));
        $(".mainDiv .segment").removeClass('segment');
        $("#segmentNumber").val(parseInt(segmentNumber)+1);
        $('.abtest').hide();
    }

    
    </script>


@endsection