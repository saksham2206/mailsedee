@if (!is_null($automation->last_error))
<div class="alert alert-warning" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
    <div style="display: flex; flex-direction: row; align-items: center;">
        <div style="margin-right:15px">
            <i class="lnr lnr-warning"></i>
        </div>
        <div style="padding-right: 40px">
            <h4>Execution Error</h4>
            <p>{{ $automation->last_error }}</p>
        </div>
    </div>
    <!-- <button class="btn bg-grey-600">Close</button> -->
</div>
@endif

<div class="sidebar-header flex-center mb-2">
    <h5 class="m-0 mr-auto">{!! $automation->name !!}</h5>
    <span class="mr-2 small">{{ trans('messages.automation.status.' . $automation->status) }}</span>
    <div>
        @include('helpers.form_control', [
            'type' => 'checkbox',
            'name' => 'automation_status',
            'label' => '',
            'class' => 'automation_status',
            'value' => ($automation->status == \Acelle\Model\AutomationList::STATUS_ACTIVE ? true : false),
            'options' => [false,true],
            'help_class' => '',
            'rules' => []
        ])
    </div>
            
</div>
<div class="d-flex align-items-center mb-4">
    <p class="pr-4 mb-0">
        {!! $automation->getIntro() !!}
    </p>
    <div>
        
    </div>         
</div>
    
<script type="text/javascript">
    $('[name="automation_status"]').change(function() {
                var value = $(this).is(":checked");
                var url, confirm;
                checkSendServer = '{{ url('automation/checkSendServer', ["uids" => $automation->uid]) }}';
                $.ajax({
                    url: checkSendServer,
                    type: 'PATCH',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    success:function(response){
                        console.log(response);
                        if(response == 1){
                            console.log(value);
                            if (value) {
                    
                                url = '{{ action('Automation2Controller@enable', ["uids" => $automation->uid]) }}';
                                confirm = '{!! trans('messages.automation.enable.confirm', ['name' => $automation->name]) !!}';
                            } else {
                                url = '{{ action('Automation2Controller@disable', ["uids" => $automation->uid]) }}';
                                confirm = '{!! trans('messages.automation.disable.confirm', ['name' => $automation->name]) !!}';
                            }
                            
                            
                            var dialog = new Dialog('confirm', {
                                message: confirm,
                                ok: function(dialog) {
                                    $.ajax({
                                        url: url,
                                        type: 'PATCH',
                                        globalError: false,
                                        data: {
                                            _token: CSRF_TOKEN
                                        }
                                    }).done(function(response) {
                                        if (!value) {
                                            notify(response.status, '{{ trans('messages.notify.success') }}', response.message);
                                        } else {
                                            var dialog = new Dialog('notification', {
                                                title: '{{ trans('messages.automation.started.title') }} <i class="lnr lnr-rocket ml-3"></i> ',
                                                message: `{{ trans('messages.automation.started.desc') }}`,
                                                ok: function(dialog) {        
                                                    sidebar.load();                    
                                                },
                                                cancel: function(dialog) {
                                                    sidebar.load();
                                                },
                                                close: function(dialog) {
                                                    sidebar.load();
                                                },
                                            });
                                        }
                                    
                                        sidebar.load();
                                    }).error(function(e) {
                                        var error = JSON.parse(e.responseText);
                                        notify('error', '{{ trans('messages.notify.error') }}', error.message);
                                        sidebar.load();
                                    });      
                                },
                                cancel: function(dialog) {
                                    sidebar.load();
                                },
                                close: function(dialog) {
                                    sidebar.load();
                                },
                            });
                        }else{
                            swal.fire('Select smtp and save automation before you can activate it !')
                            sidebar.load();
                        }
                    }
                });
                
                  
            });
</script>