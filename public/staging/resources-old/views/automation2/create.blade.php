@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <!-- <div class="col-md-2"></div> -->
        <div class="col-md-12 mb-3">
            <form id="automationCreate" action="{{ url('automation/step1Store') }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}            
        
                <h1 class="mb-20">{{ trans('messages.automation.create_automation') }}</h1>
            
                <p class="mb-10">{{ trans('messages.automation.name_your_automation') }}</p>
                
                <!-- <div class="row mb-4"> -->
                    <div class="col-md-12">
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
                <!-- </div> -->
    
                    
               

                <!-- <div class="row mb-4">
                    <div class="col-md-8">
                        
                        /*@include('helpers.form_control', [
                            'type' => 'select',
                            'include_blank' => 'Choose',
                            'label' => 'SMTP',
                            'class' => 'mail_server_connect form_control',
                            'name' => 'mail_server',
                            'value' => (is_object($automation->sendingServerList) ? $automation->sendingServerList->id : ''),
                            'options' => Auth::user()->customer->getSendingServerSelectOptions(),
                            'rules' => $automation->rules(),
                            'rules' => $automation->rules(),
                            'addExtra' => 'Connect Smtp',
                        ])*/
                    </div>
                </div> -->
                <!-- <div class="row mb-4"> -->
                   <!--  <div class="col-md-12">
                        @include('helpers.form_control', [
                            'type' => 'text',
                            'class' => 'from_name',
                            'label' => 'From Name',
                            'name' => 'from_name',
                            'value' => "",
                            'help_class' => 'automation',
                            'rules' => $automation->rules(),
                        ])
                    </div> -->
               <!--  </div> -->

               <!--  <div class="row mb-4">
                    <div class="col-md-10">
                        <div class="automation-segment">

                        </div>
                    </div>
                </div> -->
                
                <div class="text-center  mb-3">
                    <button class="btn btn-mc_primary mt-20">{{ trans('messages.automation.get_started') }}</button>
                </div>
                    
            </form>
                
        </div>
    </div>
     <div class="modal fade" id="SMTPpopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Connect Smtp</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <a href="{{ url('oauth/gmail') }}"> <img src="{{ URL::asset('images/glogo.png') }}" alt="login With Gmail" height="100px" width="100px"></a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
    <!--For Opening A popup For Conneting Server By Rajat --> 
    <script type="text/javascript">
        $(".mail_server_connect").change(function(){
            var uid = $(this).val();
            if(uid == ""){
                $("#SMTPpopup").modal('show');
                $("#SMTPpopup").removeClass('fade')

            }
        }); 
        
    </script>
       
    <script>
        // automation segment
        var automationSegment = new Box($('.automation-segment'));
        $('[name=mail_list_uid]').change(function(e) {
            var url = '{{ action('Automation2Controller@segmentSelect') }}?list_uid=' + $(this).val();

            automationSegment.load(url);
        });
        $('[name=mail_list_uid]').change();

        $('#automationCreate').submit(function(e) {
            e.preventDefault();
            
            var form = $(this);
            var url = form.attr('action');
            
            // loading effect
            createAutomationPopup.loading();
            
            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                globalError: false,
                statusCode: {
                    // validate error
                    400: function (res) {
                       createAutomationPopup.loadHtml(res.responseText);
                    }
                 },
                 success: function (res) {
                    createAutomationPopup.hide();
                    
                    addMaskLoading(res.message, function() {
                        setTimeout(function() {
                            window.location = res.url;
                        }, 1000);
                    });
                 }
            });
        });
    </script>
@endsection