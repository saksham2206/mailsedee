@extends('layouts.automation.main')

@section('title', trans('messages.automation.create'))

@section('content')
      <link href="https://app.sende.io/assetsnew/css/config/modern/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
     <!-- icons -->
        <link href="https://app.sende.io/assetsnew/css/icons.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="https://cdn.tiny.cloud/1/rnb832fakcjvuxhuesi1fsl4trkhfl30f8fz5yewgyjje1ik/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <style>
        .automation2{
            overflow: auto;
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
 .scrollbar-inner .wait a {
  position: relative;
  /*bottom: -3px;*/
}
main{
    overflow: hidden;
}
    </style>
     
    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
            <a class="navbar-brand left-logo" href="{{url('/')}}">
                @if (\Acelle\Model\Setting::get('site_logo_small'))
                    <img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
                @else
                    <img height="22" src="{{ URL::asset('images/logo_light_blue.svg') }}" alt="">
                @endif
            </a>
            <div class="d-inline-block d-flex mr-auto align-items-center"   style="font-size: .84rem;">
                <i class="material-icons-outlined automation-head-icon ml-2">alarm</i>
                {{ $automation->name }}
                
            </div>
             <a href="{{ action('Automation2Controller@delete', ['uids' => $AutomationList->uid]) }}"
                data-confirm="{{ trans('messages.automation.delete.confirm') }}"
                class="btn btn-secondary automation-delete"
            >
                <i class='fe-trash mr-1'></i> {{ trans('messages.automation.delete_automation') }}
            </a>
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

            <div class="automation-top-menu">
               @if(count($automations) < 2)    
               <a href="javascript:void(0);" class="abtest btn btn-success" onclick="AddSegment();" style="float: right;">A/B Testing</a>
               @else
               <a href="{{url('automation/changeSegment')}}/{{$diffAutomation}}" class="abtest btn btn-success" style="float: right;">Switch to Segment {{$segmentName}}</a>
               @endif
                <span class="mr-3"><i class="last_save_time" data-url="{{ action('Automation2Controller@lastSaved', $automation->uid) }}">{{ trans('messages.automation.designer.last_saved', ['time' => $automation->updated_at->diffForHumans()]) }}</i></span>
                <a href="{{ action('Automation2Controller@index') }}" class="action">
                    <i class="material-icons-outlined mr-2">arrow_back</i>
                    {{ trans('messages.automation.go_back') }}
                </a>

                <div class="switch-automation d-flex">
                    <select class="select select2 top-menu-select" name="switch_automation">
                        <option value="--hidden--"></option>
                        @foreach($AutomationList->getSwitchAutomations(Auth::user()->customer)->get() as $auto)
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

                @php
                //marker
                //echo file_exists('users/63cbd8749ee8b/home/avatar/avatar.jpg'); 
                $avatar_image_file = explode('https://app.sende.io/',Auth::user()->getProfileImageUrl())[1];
                @endphp

                @if(file_exists($avatar_image_file)) 
                    @php 
                        $image = Auth::user()->getProfileImageUrl();
                    @endphp
                @else
                    @php 
                        $image = 'https://app.sende.io/users/default-profile.png';
                    @endphp
                @endif

                <div class="account-info">
                    <ul class="navbar-nav mr-auto navbar-dark bg-dark">                        
                        <li class="nav-item dropdown">
                            <a class="account-item nav-link dropdown-toggle px-2 pro-user-name ms-1" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="avatar" src="{{ $image }}" alt="">
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
         <main role="main">
        <div class="automation2">
            <div class="diagram text-center scrollbar-inner"> 
               
                <svg id="svg" style="overflow: hidden; background: #fff;" width="5000" height="6800" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <!-- <text x="800" y="30" alignment-baseline="middle" text-anchor="middle">{{ trans('messages.automation.designer.intro') }}</text> -->

                </svg>

                
            </div>
            <!-- <div class="sidebar scrollbar-inner">
                <div class="sidebar-content">
                    
                </div>
            </div> -->
            
        </div>
    </main>
    
    <div class="modal" id="EmailTemplateModel"  class="modal-backdrop" role="dialog" aria-labelledby="EmailTemplateModel" aria-hidden="false">
      </div>

      <div class="modal " id="EmailModal" tabindex="-1" role="dialog" class="modal-backdrop" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Send Test Email</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="SendTestEMailForm">
                    <div class="form-group">
                        <label>Email</label>
                        @csrf
                        <input type="text" class="form-control" id="TestEmail" name="Email" >
                        <input type="hidden" name="templateUid" id="templateUid" value="">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                
                <button type="button" class="btn btn-success" onclick="sendTestEmail();">Send Test Email</button>
              </div>
            </div>
          </div>
        </div>

        <script type="text/javascript">
    function showEmailPopup(uid){
        //popup.hide()
        $('.close').trigger('click');
        //setTimeout(function () {
            $("#templateUid").val(uid);
            $("#EmailModal").modal('show');
        //},1000);
        
    }

    function sendTestEmail(){
        var testEmail = $("#TestEmail").val();
        var templateUid = $("#templateUid").val();
        var token = $("input[name=_token").val();

        if(testEmail == ''){
            swal.fire('Please enter test email');
        }else{
            $.ajax({
                url:"{{url('template/sendTestEmail1')}}",
                type:"POST",
                data:{"testEmail":testEmail,"templateUid":templateUid,"_token":token},
                success:function(response){
                    swal.fire('Email Sent successfully');
                }
            })
        }
    }
</script>
   
   
    <script>

      
        // timeline popup
        var timelinePopup = new Popup(undefined, undefined, {
            onclose: function() {
                // sidebar.load();
            }
        });

        // popup
        var popup = new Popup(undefined, undefined, {
            onclose: function() {
                sidebar.load();
            }
        });

        var sidebar = new Box($('.sidebar-content'));
        var lastSaved = new Box($('.last_save_time'), $('.last_save_time').attr('data-url'));

        

        function toggleHistory() {
            var his = $('.history .history-list-items');

            if (his.is(":visible")) {
                his.fadeOut();
            } else {
                his.fadeIn();
            }
        }

        function openBuilder(url) {
            var div = $('<div class="full-iframe-popup">').html('<iframe scrolling="no" class="builder d-none" src="'+url+'"></iframe>');
            
            $('body').append(div);

            // open builder effects
            addMaskLoading("{{ trans('messages.automation.template.opening_builder') }}");
            $('.builder').on("load", function() {
                removeMaskLoading();

                $(this).removeClass("d-none");
            });
        }

        function openBuilderClassic(url) {
            var div = $('<div class="full-iframe-popup">').html('<iframe scrolling="yes" class="builder d-none" src="'+url+'"></iframe>');
            
            $('body').append(div);

            // open builder effects
            addMaskLoading("{{ trans('messages.automation.template.opening_builder') }}");
            $('.builder').on("load", function() {
                removeMaskLoading();

                $(this).removeClass("d-none");
            });
        }
        
        function saveData(callback, extra = {}) {
            if (!(extra instanceof Object)) {
                alert("A hash is required");
                return false;
            }

            if ('data' in extra) {
                alert("data key is not allowed");
                return false;
            }

            var url = '{{ action('Automation2Controller@saveData', $automation->uid) }}';
        
            var postContent = {
                _token: CSRF_TOKEN,
                data: JSON.stringify(tree.toJson()),
            }

            postContent = {...extra, ...postContent};

            $.ajax({
                url: url,
                type: 'POST',
                data: postContent
            }).always(function() {
                if (callback != null) {
                    callback();
                }

                // update last saved
                lastSaved.load();
            });
        }
        
        function setAutomationName(name) {
            $('.navbar h1').html(name);
        }

        function SelectActionConfirm(key, insertToTree) {
            window.insertToTree = insertToTree;

            var url = '{{ action('Automation2Controller@actionSelectConfirm', $automation->uid) }}' + '?key=' + key;
            
            popup.load(url, function() {                
                // when click confirm select trigger type
                popup.popup.find('#action-select').submit(function(e) {
                    e.preventDefault();
                
                    var url = $(this).attr('action');
                    var data = $(this).serialize();
                    
                    // show loading effect
                    popup.loading();
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                    }).always(function(response) {
                        if (response.options.key == 'wait') {
                            var newE = new ElementWait({title: response.title, options: response.options});                            
                        } else if (response.options.key == 'condition') {
                            var newE = new ElementCondition({title: response.title, options: response.options});                            
                        }

                        insertToTree(newE);

                        newE.validate();
                        
                        // save tree
                        saveData(function() {
                            // hide popup
                            popup.hide();
                            
                            notify('success', '{{ trans('messages.notify.success') }}', response.message);
                        });
                    });
                });
            });
        }

        function EmailSetup(id) {
            var url = '{{ action('Automation2Controller@emailSetup', $automation->uid) }}' + '?action_id=' + id;
            
            popup.load(url, function() {
                // // set back event
                // popup.back = function() {
                //     Popup.hide();
                // };
            });
        }

        function OpenActionSelectPopup(insertToTree, conditionBranch = null) {

            var hasChildren = false;
            if (conditionBranch == null) {
                hasChildren = tree.getSelected().hasChildren();
            } else if (conditionBranch == 'yes') {
                hasChildren = tree.getSelected().hasChildYes();
            } else if (conditionBranch == 'no') {
                hasChildren = tree.getSelected().hasChildNo();
            }

            console.log(conditionBranch);
            if(conditionBranch == null){
               popup.load('{{ action('Automation2Controller@actionSelectPupop', $automation->uid) }}?hasChildren=' + hasChildren+'&eleId='+tree.getSelected().id+'&AutolmationListUid={{$AutomationList->uid}}', function() {
                   console.log('Select action popup loaded!');
                   
                   // // set back event
                   // popup.back = function() {
                   //     Popup.hide();
                   // };
                   
                   // when click on action type
                   popup.popup.find('.action-select-but').click(function() {
                       var key = $(this).attr('data-key');

                       if (key == 'send-an-email') {
                           // new action as email
                           var newE = new ElementAction({
                               title: '{{ trans('messages.automation.tree.action_not_set') }}',
                               options: {init: "false"}
                           });
                           
                           // add email to tree
                           insertToTree(newE);

                           // validate
                           newE.validate();

                           // save tree
                           saveData(function() {
                               notify('success', '{{ trans('messages.notify.success') }}', '{{ trans('messages.automation.email.created') }}');
                           });
                       } else {
                           // show select trigger confirm box
                           SelectActionConfirm(key, insertToTree);
                       }                    
                   });
               });
            }else if (conditionBranch == 'yes') {
                addclickYes(1,'{{$automation->uid}}',tree.getSelected().id);
            } else if (conditionBranch == 'no') {
                addclickNo(1,'{{$automation->uid}}',tree.getSelected().id);
            }
            
        }
        
        function OpenTriggerSelectPopup() {
            popup.load('{{ action('Automation2Controller@triggerSelectPupop', $automation->uid) }}', function() {
                console.log('Select trigger popup loaded!');
                
                // // set back event
                // popup.back = function() {
                //     Popup.hide();
                // };
                
                // when click on trigger type
                popup.popup.find('.trigger-select-but').click(function() {
                    var key = $(this).attr('data-key');
                    
                    // show select trigger confirm box
                    SelectTriggerConfirm(key);
                });
            });
        }
        
        function SelectTriggerConfirm(key) {
            var url = '{{ action('Automation2Controller@triggerSelectConfirm', $automation->uid) }}' + '?key=' + key;
            
            popup.load(url, function() {
                console.log('Confirm trigger type popup loaded!');
                
                // set back event
                popup.back = function() {
                    OpenTriggerSelectPopup();
                };
            });
        }
        
        function EditTrigger(url) {
            popup.load(url, function() {
                // set back event
                popup.back = function() {
                    Popup.hide();
                };
                    
            });
        }
        
        function EditAction(url) {
             popup.load(url, function() {
                // set back event
                popup.back = function() {
                    Popup.hide();
                };
                    
            });
        }
    
        $(document).ready(function() {
            // load sidebar
            sidebar.load('{{ action('Automation2Controller@settings', $AutomationList->uid) }}');

            // history toggle
            $('.diagram .history .history-list').click(function() {
                toggleHistory();
            });
            $(document).mouseup(function(e) 
            {
                var container = $(".history .history-list-items");

                // if the target of the click isn't the container nor a descendant of the container
                if (!container.is(e.target) && container.has(e.target).length === 0) 
                {
                    container.fadeOut();
                }
            });

            // switch automation
            $('[name=switch_automation]').change(function() {
                var val = $(this).val();
                var text = $('[name=switch_automation] option:selected').text();
                var confirm = "{{ trans('messages.automation.switch_automation.confirm') }} <span class='font-weight-semibold'>" + text + "</span>"; 

                var dialog = new Dialog('confirm', {
                    message: confirm,
                    ok: function(dialog) {
                        window.location = val; 
                    },
                    cancel: function() {
                        $('[name=switch_automation]').val('');
                    },
                    close: function() {
                        $('[name=switch_automation]').val('');
                    },
                });
            });
            $('.select2-results__option').each

            // fake history
            $('.diagram .history .history-list-items a, .history .history-undo').click(function(e) {
                e.preventDefault();

                var dialog = new Dialog('alert', {
                    message: 'Automation is already finallized. Cannot rollback to previous state.',
                });
            });
            
            // quota view
            $('.quota-view').click(function(e) {
                e.preventDefault();

                var url = $(this).attr('href');

                popup.load(url, function() {
                    console.log('quota popup loaded!');
                });
            });
        });

        var tree;

        function doSelect(e) {
            // TODO 1:
            // Gọi Ajax to Automation2@action
            // Prams: e.getId()
            // Trả về thông tin chi tiết của action để load nội dung bên phải
            // Trên server: gọi hàm model: Automation2::getActionInfo(id)
            e.select(); // highlight
            
            console.log(e.getType());
            
            // if click on a trigger
            if (e.getType() == 'ElementTrigger') {
                var options = e.getOptions();
                
                // check if trigger is not init
               // alert(options.init);
                if (options.init == "false") {
                    
                    OpenTriggerSelectPopup();
                }
                // trigger was init
                else {
                    
                    var url = '{{ action('Automation2Controller@triggerEdit', $automation->uid) }}' + '?key=' + e.getOptions().key + '&id=' + e.getId();
                    console.log(url);
                    // Open trigger types select list
                    EditTrigger(url);
                }
            }
            // is WAIT
            else if (e.getType() == 'ElementWait') {
                    var url = '{{ action('Automation2Controller@actionEdit', $automation->uid) }}' + '?key=' + e.getOptions().key + '&id=' + e.getId();
                    
                    // Open trigger types select list
                    EditAction(url);
            }
            // is Condition
            else if (e.getType() == 'ElementCondition') {
                    var url = '{{ action('Automation2Controller@actionEdit', $automation->uid) }}' + '?key=' + e.getOptions().key + '&id=' + e.getId();
                    
                    // Open trigger types select list
                    EditAction(url);
            }
            // is Email
            else if (e.getType() == 'ElementAction') {
                if (e.getOptions().init == "true") {
                    var type = $(this).attr('data-type');
                    var url = '{{ action('Automation2Controller@email', $automation->uid) }}?email_uid=' + e.getOptions().email_uid;
                    
                    // Open trigger types select list
                  //  console.log(url);
                    EditAction(url);
                } else {
                    // show select trigger confirm box
                    EmailSetup(e.getId());
                }
            }
            // is Email
            else if (e.getType() == 'ElementOperation') {
                var type = $(this).attr('data-type');
                var url = '{{ action('Automation2Controller@operationShow', $automation->uid) }}?operation=' + e.getOptions().operation_type + '&id=' + e.getId();
                
                // Open trigger types select list
                sidebar.load(url);
            }
        }

        (function() {
            //var json = [
            //    {title: "Click to choose a trigger", id: "trigger", type: "ElementTrigger", options: {init: false}}
            //];
            
            @if ($automation->data)

                var json = {!! $automation->getData() !!};
            @else
                var json = [
                    {title: "Click to choose a trigger", id: "trigger", type: "ElementTrigger", options: {init: "false"}}
                ];
            @endif

            var container = document.getElementById('svg');

            tree = AutomationElement.fromJson(json, container, {
                onclick: function(e) {
                    doSelect(e);
                },

                onhover: function(e) {
                    console.log(e.title + " hovered!");
                },

                onadd: function(e) {

                    e.select();

                    //console.log(e.child.id);
                    if(e.child == null){
                        var html = '<div class="btn-group btn_center two_btns" id="" role="group" aria-label="Basic example"><button type="button" class="btn btn-secondary" onclick="followup(1,\''+e.id+'\');">Follow Up</button><button type="button" class="btn btn-secondary" onclick="addclick(1,\''+e.id+'\)">Open</button></div>';
                        console.log($('#'+e.id+'-plus'));
                        $('#'+e.id+'-plus').after(html);
                        OpenActionSelectPopup(function(element) {
                            e.insert(element);
                            e.getTrigger().organize();

                            // select new element
                            doSelect(element);
                        });
                    }
                    
                },

                onaddyes: function(e) {
                    e.select();
                    OpenActionSelectPopup(function(element) {
                        e.insertYes(element);
                        e.getTrigger().organize();

                        // select new element
                        doSelect(element);
                    }, 'yes');
                },

                onaddno: function(e) {
                    e.select();
                    OpenActionSelectPopup(function(element) {
                        e.insertNo(element);
                        e.getTrigger().organize();

                        // select new element
                        doSelect(element);
                    }, 'no');
                },

                validate: function(e) {
                    if (e.getType() == 'ElementTrigger') {
                        if (e.getOptions()['init'] == null || !(e.getOptions()['init'] == "true" || e.getOptions()['init'] == true)) {
                            e.showNotice('{{ trans('messages.automation.trigger.is_not_setup') }}');
                            e.setTitle('{{ trans('messages.automation.trigger.is_not_setup.title') }}');
                        } else {
                            e.hideNotice();
                            // e.setTitle('Correct title goes here');
                        }
                    }

                    if (e.getType() == 'ElementAction') {
                        if (e.getOptions()['init'] == null || !(e.getOptions()['init'] == "true" || e.getOptions()['init'] == true)) {
                            e.showNotice('{{ trans('messages.automation.email.is_not_setup') }}');
                            e.setTitle('{{ trans('messages.automation.email.is_not_setup.title') }}');
                        } else if (e.getOptions()['template'] == null || !(e.getOptions()['template'] == "true" || e.getOptions()['template'] == true)) {
                            e.showNotice('{{ trans('messages.automation.email.has_no_content') }}');
                        } else {
                            e.hideNotice();
                            // e.setTitle('Correct title goes here');
                        }
                    }

                    if (e.getType() == 'ElementCondition') {
                        if     (      e.getOptions()['type'] == null || 
                                 (e.getOptions()['type'] == 'click' && e.getOptions()['email_link'] == null ) ||
                                (e.getOptions()['type'] == 'open' && e.getOptions()['email'] == null ) || 
                                (e.getOptions()['type'] == 'cart_buy_item' && !e.getOptions()['item_id'] )
                            ) {
                            e.showNotice('Condition not set up yet');
                            e.setTitle('Condition not set up yet');
                        } else {
                            e.hideNotice();
                            // e.setTitle('Correct title goes here');
                        }
                    }
                }
            });
            console.log(tree);
            @if (request()->auto_popup)
                // console.log(tree.child.getOptions());
                doSelect(tree.child);
                setTimeout(function() {
                    popup.load('https://product.com/automation2/{{ $automation->uid }}/email/setup?email_uid=' + tree.child.getOptions().email_uid,
                    function() {
                        $('.email_title').html('{{ trans('messages.source.abandoned_cart_email') }}');
                    });
                }, 100);

                popup.onHide = function() {
                    if (parent && parent.$('.trans_frame')) {
                        parent.$('.trans_frame').fadeOut();
                    }
                    parent.hidden = true;

                    // parent
                    parent.$('html').css('overflow', 'auto');

                    doSelect(tree.child);
                    setTimeout(function() {
                        popup.load('https://product.com/automation2/{{ $automation->uid }}/email/setup?email_uid=' + tree.child.getOptions().email_uid,
                        function() {
                            $('.email_title').html('{{ trans('messages.source.abandoned_cart_email') }}');
                        });
                    }, 100);

                    parent.jReload();
                };
            @endif

        })();
        console.log(tree);
    </script>

    <script type="text/javascript">

        function createSeq(id='',uid='',elementId=''){
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

       function createSegment(id='',uid='',elementId=''){
      // doSelect2();
       $.ajax({
           url: "{{url('automation/createSegment')}}/{{$AutomationList->uid}}",
           data:{'type':'initial','id':id,'uid':uid,'elementId':elementId},
           success:function(response){
               window.location.href = response.url;
   
           }
       });
       
   }
   function showEmailTemplatePopup(id,uid){
       var uid = "{{ $AutomationList->uid }}";
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
                                       data:{'type':'initial','id':id,'uid':uid},
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
                           data:{'type':'initial','id':id,'uid':uid},
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
   
   function followup(id,uid,elementId){
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
   
   $(".segment").clone().appendTo('.mainDiv');
   $(".mainDiv .segment").removeClass('hide');
   var segmentNumber = $("#segmentNumber").val();
   $(".mainDiv .segment").attr('id','segment_'+(parseInt(segmentNumber)+1));
   //$(".mainDiv .segment").addClass('col-sm-12');
   var funcNmae = "showEmailTemplatePopup("+(parseInt(segmentNumber)+1)+")";
   $(".mainDiv .segment  > .data").attr('id','data_'+(parseInt(segmentNumber)+1));
   
   $("#data_"+(parseInt(segmentNumber)+1)).html('<h2 class="heading-top">segment 2</h2>')
   $(".mainDiv .segment").append('<input type="hidden" name="sequence-steps" class="sequence-steps" id="sequence-steps_'+(parseInt(segmentNumber)+1)+'" value="0">');
   $(".mainDiv .segment  > .create_sequence_btn").attr('onclick',funcNmae);
   $(".mainDiv .segment  > .create_sequence_btn").addClass('create_sequence_btn'+(parseInt(segmentNumber)+1));
   $(".mainDiv .segment").addClass('col-md-6 col-sm-12');
   $(".mainDiv .segment ").removeClass('segment');
   $("#segmentNumber").val(parseInt(segmentNumber)+1);
   createSegment();
   $('.abtest').hide();
   }
   
   // function doSelect2(url){
   //         var id = setId();
   //         var url = '{{ action('Automation2Controller@actionSelectConfirm', $AutomationList->uid) }}' + '?key=wait&id=' + id;
               
   //                 // Open trigger types select list
   //                 EditTrigger(url);
   //     }
   
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
<script type="text/javascript">
    $('[name="automation_status"]').change(function() {
                var value = $(this).is(":checked");
                var url, confirm;
                checkSendServer = '{{ url('automation/checkSendServer', ["uids" => $AutomationList->uid]) }}';
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
                    
                                url = '{{ action('Automation2Controller@enable', ["uids" => $AutomationList->uid]) }}';
                                confirm = '{!! trans('messages.automation.enable.confirm', ['name' => $automation->name]) !!}';
                            } else {
                                url = '{{ action('Automation2Controller@disable', ["uids" => $AutomationList->uid]) }}';
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
@endsection
