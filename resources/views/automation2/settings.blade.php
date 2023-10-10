@include('automation2._info')
				
@include('automation2._tabs', ['tab' => 'settings'])
    
<p class="mt-3">
    {!! trans('messages.automation.settings.intro') !!}
</p>
    
<form id="automationUpdate" action="{{ action("Automation2Controller@update", $automation->uid) }}" method="POST" class="form-validate-jqueryz">
    {{ csrf_field() }}
    
    <div class="row mb-3">
        <div class="col-md-9">
            @include('helpers.form_control', [
                'type' => 'text',
                'class' => '',
                'label' => trans('messages.automation.automation_name'),
                'name' => 'name',
                'value' => $automation->name,
                'help_class' => 'automation',
                'rules' => $automation->rules(),
            ])
            
            @include('helpers.form_control', [
                'name' => 'mail_list_uid',
                'include_blank' => trans('messages.automation.choose_list'),
                'type' => 'select',
                'label' => trans('messages.automation.change_mail_list'),
                'value' => (is_object($automation->mailList) ? $automation->mailList->uid : ''),
                'options' => Auth::user()->customer->readCache('MailListSelectOptions', []),
                'rules' => $automation->rules(),
            ])

            <div class="automation-segment">

            </div>
            
            @include('helpers.form_control', [
                'type' => 'select',
                'name' => 'timezone',
                'value' => \Auth::user()->customer->timezone,
                'options' => Tool::getTimezoneSelectOptions(),
                'include_blank' => trans('messages.choose'),
                'rules' => $automation->rules(),
                'disabled' => true,
            ])
            @include('helpers.form_control', [
                'type' => 'select',
                'include_blank' => 'Choose',
                'label' => 'SMTP',
                'class' => 'mail_server_connect form_control',
                'name' => 'mail_server',
                'value' => (!empty($automation->smtp_server_id) ? $automation->smtp_server_id : ''),
                'options' => Auth::user()->customer->getSendingServerSelectOptions(),
                'rules' => $automation->rules(),
                'rules' => $automation->rules(),
                'addExtra' => 'Connect Smtp',
            ])
        </div>
    </div>

    
    <button class="btn btn-success mt-20">{{ trans('messages.automation.settings.save') }}</button>            
</form>

<div class="mt-4 d-flex py-3">
    <div>
        <h5 class="mb-2">
            {{ trans('messages.automation.dangerous_zone') }}
        </h5>
        <p class="">
            {{ trans('messages.automation.delete.wording') }}        
        </p>
        <div class="mt-3">
            <a href="{{ action('Automation2Controller@delete', ['uids' => $automation->uid]) }}"
                data-confirm="{{ trans('messages.automation.delete.confirm') }}"
                class="btn btn-secondary automation-delete"
            >
                <i class='lnr lnr-trash mr-1'></i> {{ trans('messages.automation.delete_automation') }}
            </a>
        </div>
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
      <div class="modal-body smpt_logo">
        @php
        $gmailUrl = url('oauth/gmail');
        $microsoftUrl = url('automation/graphLogin');
        @endphp
        <a href="javascript:void(0);" onclick="openNewWindow('{{ $gmailUrl }}');" > <img src="{{ URL::asset('images/glogo.png') }}" alt="login With Gmail"  width="100px"></a>
        <a href="javascript:void(0);" onclick="openNewWindow('{{ $microsoftUrl }}');" > <img src="{{ URL::asset('images/outlook_logo.jpg') }}" alt="login With Microsoft"  width="100px"></a>
        @foreach (Auth::user()->customer->getSendingServertypes() as $key => $type)
        @php
        $Url = action('SendingServerController@create', ["type" => $key]);
        @endphp
            <a href="javascript:void(0);" onclick="openNewWindow('{{ $Url }}');" >
                @if($key == 'smtp')
                            <img src="{{ URL::asset('images/smtp-com.png') }}"  width="100px">
                @endif
                @if($key == 'sendmail')
                            <img src="{{ URL::asset('images/sendmail-logo.webp') }}"  width="100px">
                @endif

                        </a>
         @endforeach
        <!-- <ul class="modern-listing big-icon no-top-border-list mt-0"> -->

               <!--  @foreach (Auth::user()->customer->getSendingServertypes() as $key => $type)
                    <li>
                        <a href="{{ action('SendingServerController@create', ["type" => $key]) }}" class="btn btn-info bg-info-800">{{ trans('messages.choose') }}</a>
                        
                        <h4><a href="{{ action('SendingServerController@create', ["type" => $key]) }}">{{ trans('messages.' . $key) }}</a></h4>
                        <p>
                            {{ trans('messages.sending_server_intro_' . $key) }}
                        </p>
                    </li>

                @endforeach -->
                <!-- <li>
                    <a href="javascript:void(0);" onclick="openNewWindow();" class="btn btn-info bg-info-800"> {{ trans('messages.choose') }}</a>
                    <a href="javascript:void(0);" onclick="openNewWindow();"> <span class="server-avatar ">
                                <i class="icon-server"><img src="{{ URL::asset('images/glogo.png') }}" alt="login With Gmail" height="30px" width="30px"></i>
                            </span></a>
                    <h4><a href="javascript:void(0);" onclick="openNewWindow();">Gmail</a></h4>
                    <p>Send emails through your gmail account</p>
                   
                </li>
 -->
            <!-- </ul> -->
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
        $('#SMTPpopup').on('hidden.bs.modal', function () {
          sidebar.load();
        })
        
    </script>
    <script type="text/javascript">
        function openNewWindow(urlData){
            //urlData =  "{{ url('oauth/gmail') }}";
            window.open(urlData);
        }
    </script>
    
<script>
    // automation segment
    var automationSegment = new Box($('.automation-segment'));
    $('[name=mail_list_uid]').change(function(e) {
        var url = '{{ action('Automation2Controller@segmentSelect') }}?uid={{ $automation->uid }}&list_uid=' + $(this).val();

        automationSegment.load(url);
    });
    $('[name=mail_list_uid]').change();


    // set automation name
    setAutomationName('{{ $automation->name }}');

    $('#automationUpdate').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        
        // loading effect
        sidebar.loading();
        
        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            statusCode: {
                // validate error
                400: function (res) {
                   sidebar.loadHtml(res.responseText);
                }
             },
             success: function (response) {
                sidebar.load();
                
                notify(response.status, '{{ trans('messages.notify.success') }}', response.message);
             }
        });
    });

    var $sel = $('[name=mail_list_uid]').on('change', function() {
        if ($sel.data('confirm') == 'false') {
            confirm = `{{ trans('messages.automation.change_list.confirm') }}`;

            var dialog = new Dialog('confirm', {
                message: confirm,
                ok: function(dialog) {
                    // store new value        
                    $sel.trigger('update');     
                },
                cancel: function(dialog) {
                    // reset
                    $sel.trigger('restore');
                },
                close: function(dialog) {
                    // reset
                    $sel.trigger('restore');
                },
            });
        }
    }).on('restore', function() {
        $(this).data('confirm', 'true');
        $(this).val($(this).data('currVal')).change();
        $(this).data('confirm', 'false');
    }).on('update', function() {
        $(this).data('currVal', $(this).val());
        $(this).data('confirm', 'false');
    }).trigger('update');

    $('.automation-delete').click(function(e) {
        e.preventDefault();
        
        var confirm = $(this).attr('data-confirm');
        var url = $(this).attr('href');

        var dialog = new Dialog('confirm', {
            message: confirm,
            ok: function(dialog) {
                //
                $.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: CSRF_TOKEN,
                    },
                    statusCode: {
                        // validate error
                        400: function (res) {
                            console.log('Something went wrong!');
                        }
                    },
                    success: function (response) {
                        addMaskLoading(
                            '{{ trans('messages.automation.redirect_to_index') }}',
                            function() {
                                window.location = '{{ action('Automation2Controller@index') }}';
                            },
                            { wait: 2000 }
                        );

                        // notify
                        notify('success', '{{ trans('messages.notify.success') }}', response.message);
                    }
                });
            },
        });
    });
</script>
