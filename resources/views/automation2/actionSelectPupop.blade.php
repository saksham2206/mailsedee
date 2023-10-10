@extends('layouts.popup.small')

@section('content')
 <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h3 class="mb-3">{{ trans('messages.automation.add_an_action') }}</h3>
            
                
            <!-- <div class="line-list">
                @foreach ($types as $type)
                    @php
                        $disabled = ($type == 'condition' && $hasChildren == "true") ? 'd-disabled' : '';
                    @endphp
                    <div class="line-item action-select-but action-select-{{ $type }} {{ $disabled }}" data-key="{{ $type }}">
                        <div class="line-icon">
                            <i class="lnr lnr-{{ trans('messages.automation.action.' . $type . '.icon') }}"></i>
                        </div>
                        <div class="line-body">
                            <h5>{{ trans('messages.automation.action.' . $type) }}</h5>
                            <p>{{ trans('messages.automation.action.' . $type . '.desc') }}</p>
                            @if ($type == 'condition' && $hasChildren == "true")
                                <p class="text-warning small mt-1">
                                    <i class="material-icons-outlined">warning</i> {{ trans('messages.automation.action.can_not_add_condition') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div> -->
            @if($eleid == 'trigger')
            <button type="button" class="btn btn-secondary" onclick="createSeq(1,'{{$automation->uid}}','{{$eleid}}');">Create Mail</button>
            @else
            <button type="button" class="btn btn-secondary" onclick="followup(1,'{{$automation->uid}}','{{$eleid}}');">Follow Up</button>
            <button type="button" class="btn btn-secondary" onclick="addclick(1,'{{$automation->uid}}','{{$eleid}}')">Open</button>
            @endif
            
                                                
        </div>
    </div>
    <script>
        $('.action-select-operation').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var url = '{!! action('Automation2Controller@operationSelect', $automation->uid) !!}';
            popup.load(url);
        });
    </script>

    <script type="text/javascript">
   
   
   function submitTemplateForm(){
       //$("#templateForm").e.preventDefault();
       //CKEDITOR.instances.content.updateElement();
       var TempID = $("#templateSelect").val();
       var url ='';
       var myContent = tinymce.activeEditor.getContent();
       $('.contents').val(myContent);
       console.log(myContent);
       if(TempID == 'Select Template'){
           url = "{{ url('automation/storeTemplate') }}";
       }else{
           url = "{{ url('automation/getTempalte') }}"
       }
        console.log(TempID);
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
                   $('.mainDiv').removeClass('hide');
                       $('#startDiv').addClass('hide');
                   var squencenumber = $("#sequence-steps_"+obj.sequenceId).val();
                   
                   var newSequence = parseInt(squencenumber) + 1;
                   if(obj.type == 'initial'){
                       var token = $("input[name=_token]").val();
                       $.ajax({
                           url : "{{ url('automation/createAutomation') }}",
                           method:"POST",
                           data:{"uid":obj.uid,"templateUid":obj.data.uid,"_token":token,"type":obj.type,"elementId":obj.elementId},
                           success:function(response){
                            tinymce.remove()

                               window.location.reload();
                           }
                       });
                       uid = setId();
                       child = null; 
                   }else{
                       var token = $("input[name=_token]").val();
                       $.ajax({
                           url : "{{ url('automation/updateAutomation') }}",
                           method:"POST",
                           data:{"uid":obj.uid,"templateUid":obj.data.uid,"_token":token,"type":obj.type,"clickType":obj.clickType,"elementId":obj.elementId},
                           success:function(response){
                            tinymce.remove()

                               window.location.reload();
                           }
                       });
                       uid = setId();
                       child = null;
                       $("#child_"+squencenumber).val(uid);
                   }
                   // $("#sequence-steps_"+obj.sequenceId).val(newSequence);
                   
                   // var html = '<div class="col-md-12">'
                   //      html += '<div class="form-group margin-bottom"><div class="fav-mail"><i class="fa fa-envelope text-info"></i><span>'+obj.data.subject+'</span><button type="button" class="btn btn-edit">Edit</button><div class="comment"><p>'+obj.data.content+'</p></div></div><div class="footer-sec"><h3>1 REMAINING</h3></div><div class="border-section"></div></div><div class="border-plus"><i class="fa fa-plus" onclick="showbtngroup('+obj.sequenceId+newSequence+');"></i></div>';
                   
                   // html += "<input type='hidden' name='id["+obj.sequenceId+"][]' id='uid_"+newSequence+"' value='"+uid+"'>";
                   // html += "<input type='hidden' name='child["+obj.sequenceId+"][]' id='child_"+newSequence+"' value='"+child+"'>";
                   // html += "<input type='hidden' name='template_uid["+obj.sequenceId+"][]' id='template_uid_"+newSequence+"' value='"+obj.data.uid+"'>";
                   
                   // html += "<input type='hidden' name='type["+obj.sequenceId+"][]' id='type_"+newSequence+"' value='"+obj.type+"'>";
                   //html +="<div class='title-section'>"+obj.data.subject+"</div>";
                   //html +="<div class='content-section'>"+obj.data.content+" </div>";
                   //html +='<button type="button" class="btn btn-success" onclick="showbtngroup('+newSequence+');"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"></path></svg></button>';
                   // html += '<div class="btn-group btn_center" id="btngrp_'+obj.sequenceId+newSequence+'" role="group" aria-label="Basic example"><button type="button" class="btn btn-secondary" onclick="followup('+obj.sequenceId+');">Follow Up</button><button type="button" class="btn btn-secondary" onclick="addclick('+obj.sequenceId+')">click</button></div>'
                   // html +="</div>";
                   // var oldId = newSequence-1
                   // $("#btngrp_"+obj.sequenceId+oldId).remove();
                   // $("#data_"+obj.sequenceId).append(html);
   
                   // $("#btngrp_"+obj.sequenceId+newSequence).hide();
                   // $(".btn_center").hide();
                   // $('.create_sequence_btn'+obj.sequenceId).css('display', 'none');
                   // $("#EmailTemplateModel").modal('hide');
                   // $('#submitButton').removeClass('hide');
   
               }else{
                   alert(obj.msg)
               }
           }
       });
   }
   
   function showbtngroup(id){
       $("#btngrp_"+id).show();
   }

   function createSeq(id='',uid='',elementId=''){
    $(".close").trigger('click');
      // doSelect2();
       
       if(id == 1){
          $.ajax({
               url: "{{url('automation/checkSubscriber')}}/{{ $AutomationList->uid }}",
               success:function(response){
                   if(response == true){
                       $.ajax({
                           url:"{{url('automation/checkSendServer')}}/{{ $AutomationList->uid }}",
                           type: 'PATCH',
                           data: {
                               _token: CSRF_TOKEN
                           },
                           success:function(res){
                               if(res == true){
                                   $.ajax({
                                       url: "{{url('automation/createSequenceTemplate')}}",
                                       data:{'type':'initial','id':id,'uid':uid,'elementId':elementId},
                                       success:function(response){
                                           $("#EmailTemplateModel").html(response);
                                           $("#EmailTemplateModel").modal('show');
                                           $("#EmailTemplateModel").removeClass('fade');
   
                                       }
                                   });
                               }else{
                                   sidebar.load('{{ action('Automation2Controller@settings', $AutomationList->uid) }}');
                                   swal.fire('Please Select Sending Server First And Save It');
                               }
                           }
                       })
                       
                   }else{
                       $("#imports").trigger('click');
                       $("#importsli").trigger('click');
                       swal.fire('Please Add Subscriber First');
                   }
               }
           }); 
       }else{
          $.ajax({
               url: "{{url('automation/checkSubscriberSgment2')}}/{{ $AutomationList->uid }}",
               success:function(response){
                   if(response == true){
                       $.ajax({
                           url: "{{url('automation/createSequenceTemplate')}}",
                           data:{'type':'initial','id':id,'uid':uid,'elementId':elementId},
                           success:function(response){
                               $("#EmailTemplateModel").html(response);
                               $("#EmailTemplateModel").modal('show');
                               $("#EmailTemplateModel").removeClass('fade');
   
                           }
                       });
                   }else{
                       $("#imports").trigger('click');
                       $("#importsli").trigger('click');
                       swal.fire('Please Add  1 more Subscriber First');
                   }
               }
           });
       }
       
   }
   
   function followup(id,uid,elementId){
    $(".close").trigger('click');
      // doSelect2();
       $.ajax({
           url: "{{url('automation/createSequenceTemplate')}}",
           data:{'type':'followup','id':id,'uid':uid,'elementId':elementId},
           success:function(response){
               $("#EmailTemplateModel").html(response);
               $("#EmailTemplateModel").modal('show');
               $("#btngrp_"+id).hide();
   
           }
       });
       
   }
   function addclick(id,uid,elementId){

       $.ajax({
           url: "{{url('automation/createOpenSturcture')}}",
           data:{'type':'click','id':id,'uid':uid,'elementId':elementId},
           success:function(response){
                window.location.reload();
           }
       });
       
   }

   function addclickYes(id,uid,elementId){
    $(".close").trigger('click');
       $.ajax({
           url: "{{url('automation/createSequenceTemplate')}}",
           data:{'type':'click','id':id,'uid':uid,'clickType':'Yes','elementId':elementId},
           success:function(response){
                $("#EmailTemplateModel").html(response);
               $("#EmailTemplateModel").modal('show');
               $("#btngrp_"+id).hide();
           }
       });
       
   }

   function addclickNo(id,uid,elementId){
    $(".close").trigger('click');
       $.ajax({
           url: "{{url('automation/createSequenceTemplate')}}",
           data:{'type':'click','id':id,'uid':uid,'clickType':'No','elementId':elementId},
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
   
   // $(".segment").clone().appendTo('.mainDiv');
   // $(".mainDiv .segment").removeClass('hide');
   // var segmentNumber = $("#segmentNumber").val();
   // $(".mainDiv .segment").attr('id','segment_'+(parseInt(segmentNumber)+1));
   // //$(".mainDiv .segment").addClass('col-sm-12');
   // var funcNmae = "showEmailTemplatePopup("+(parseInt(segmentNumber)+1)+")";
   // $(".mainDiv .segment  > .data").attr('id','data_'+(parseInt(segmentNumber)+1));
   
   // $("#data_"+(parseInt(segmentNumber)+1)).html('<h2 class="heading-top">segment 2</h2>')
   // $(".mainDiv .segment").append('<input type="hidden" name="sequence-steps" class="sequence-steps" id="sequence-steps_'+(parseInt(segmentNumber)+1)+'" value="0">');
   // $(".mainDiv .segment  > .create_sequence_btn").attr('onclick',funcNmae);
   // $(".mainDiv .segment  > .create_sequence_btn").addClass('create_sequence_btn'+(parseInt(segmentNumber)+1));
   // $(".mainDiv .segment").addClass('col-md-6 col-sm-12');
   // $(".mainDiv .segment ").removeClass('segment');
   // $("#segmentNumber").val(parseInt(segmentNumber)+1);
   createSeq();
   $('.abtest').hide();
   }
   
   
   
   function showAddTime(uid){
   $.ajax({
       url : "{{url('automation/showAddTime')}}/"+uid,
       success:function(response){
           $('#StartTimeModel').html(response);
           $('#StartTimeModel').modal('show');
       }
   })
   }
   
   function EditTemplate(template_id,email_id){
   $.ajax({
       url:"{{url('automation/EditTemplate')}}/"+template_id+'/'+email_id,
       success:function(response){
           $("#EmailTemplateModel").html(response);
           $("#EmailTemplateModel").modal('show');
       }
   })
   }
   
   function updateTemplateForm(){
       //$("#templateForm").e.preventDefault();
       CKEDITOR.instances.content.updateElement();
       var TempID = $("#templateSelect").val();
       var url ='';
       if(TempID != ''){
           url = "{{ url('automation/updateTemplate') }}";
       }else{
           url = "{{ url('automation/updateTemplate') }}"
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
                   //alert('hi');
                   window.location.reload();
               }else{
                   swal.fire(obj.msg)
               }
           }
       });
   }
   
   function chnageDay(key,uid){
   $.ajax({
       url :"{{url('automation/getDay')}}/"+key+"/"+uid,
       success:function(response){
           $('#ChangeDayModel').html(response);
           $('#ChangeDayModel').modal('show');
       }
   })
   }
   
   function chnageDay1(key,uid){
   $.ajax({
       url :"{{url('automation/getDay1')}}/"+key+"/"+uid,
       success:function(response){
           $('#ChangeDayModel').html(response);
           $('#ChangeDayModel').modal('show');
       }
   })
   }
   
   function deleteSegment(uid) {
   var proceed = confirm("Are you sure you want to proceed?");
   if (proceed) {
     $.ajax({
           url:"{{url('automation/deleteSegment')}}/"+uid,
           success:function(response){
               //obj = JSON.parse(response);
               if(response.status == true){
                   swal.fire('Delete successfully');
                   window.location.reload();
                   
               }else{
                  swal.fire('You can not deleted This segment because it\'s already running.'); 
               }
               
           }
       })
   } 
   
   }
   
   function deleteSequence(key, uid) {
   var proceed = confirm("Are you sure you want to proceed?");
   if (proceed) {
       if(key == 1){
           $.ajax({
               url:"{{url('automation/deleteSegment')}}/"+uid,
               success:function(response){
                   if(response.status == true){
                       swal.fire('Delete successfully');
                       window.location.reload();
                       
                   }else{
                      swal.fire('You can not deleted This sequence because it\'s already running.'); 
                   }
               }
           })
       }else{
           $.ajax({
               url:"{{url('automation/deleteSequence')}}/"+uid+"/"+key,
               success:function(response){
                   if(response.status == true){
                       swal.fire('Delete successfully');
                       window.location.reload();
                       
                   }else{
                      swal.fire('You can not deleted This sequence because it\'s already running.'); 
                   }
               }
           })
       }
   }
   
   }
   
</script>

@endsection
