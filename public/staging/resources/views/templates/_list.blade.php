<!-- /core JS files -->

        <link rel="stylesheet" type="text/css" href="{{URL::asset('assetsnew/libs/sweetalert2/sweetalert2.min.css')}}">
    <script type="text/javascript" src="{{URL::asset('assetsnew/libs/sweetalert2/sweetalert2.min.js')}}"></script>
@if ($templates->count() > 0)
    <table class="table pml-table table-striped"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}">
        @foreach ($templates as $key => $template)
            <tr>
                <td width="1%">
                    <div class="text-nowrap">
                        <div class="checkbox inline">
                            <label>
                                <input type="checkbox" class="node styled"
                                    name="ids[]"
                                    value="{{ $template->uid }}"
                                />
                            </label>
                        </div>
                    </div>
                </td>
                
                <td>
                    <h5 class="no-margin text-bold">
                        <a class="kq_search" href="#" onclick="popupwindow('{{ action('TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)">
                            {{ $template->name }}
                        </a>
                    </h5>
                    
                    <span class="text-muted">{{ trans('messages.updated_at') }}: {{ Tool::formatDateTime($template->created_at) }}</span>
                </td>

               

                <td class="text-right">
                    <!-- @if (Auth::user()->customer->can('update', $template))
                        @if (in_array(Acelle\Model\Setting::get('builder'), ['both','pro']) && $template->builder)
                            <a href="{{ action('TemplateController@builderEdit', $template->uid) }}" type="button" class="btn btn-default btn-icon template-compose">
                                {{ trans('messages.template.pro_builder') }}
                            </a>
                        @endif
                        @if (in_array(Acelle\Model\Setting::get('builder'), ['both','classic']))
                            <a href="{{ action('TemplateController@edit', $template->uid) }}" type="button" class="btn bg-grey btn-icon template-compose-classic">
                                {{ trans('messages.template.classic_builder') }}
                            </a>
                        @endif
                    @endif -->
                    @if (Auth::user()->customer->can('preview', $template) ||
                        Auth::user()->customer->can('copy', $template) ||
                        Auth::user()->customer->can('delete', $template) ||
                        Auth::user()->customer->can('update', $template))
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle btn-info" data-toggle="dropdown">Action<span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @if (Auth::user()->customer->can('preview', $template))
                                    <li class="user_fristicon"><a href="#preview" onclick="popupwindow('{{ action('TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)"><i class="icon-zoomin3"></i> {{ trans("messages.preview") }}</a></li>
                                @endif
                                @if (Auth::user()->customer->can('update', $template))
                                   <!--  <li>
                                        <a class="upload-thumb-button" href="{{ action('TemplateController@updateThumb', $template->uid) }}">
                                            <i class="icon-file-picture"></i> {{ trans("messages.template.upload_thumbnail") }}
                                        </a>
                                    </li> -->
                                @endif
                                
                                @if (Auth::user()->customer->can('copy', $template))
                                    <li class="user_fristicon">
                                        <a
                                            href="{{ action('TemplateController@copy', $template->uid) }}"
                                            type="button"
                                            class="modal_link"
                                            data-method="GET"
                                        >
                                            <i class="icon-copy4"></i> {{ trans("messages.template.copy") }}
                                        </a>
                                    </li>
                                @endif
                                <li class="user_fristicon">
                                        <a class="" href="{{ action('TemplateController@editTemplate',$template->uid ) }}">
                                            <i class="icon-pencil7"></i> Edit
                                        </a>
                                    </li>
                                    <li class="user_fristicon">
                                        <a class="" href="javascript:void(0);" onclick="showEmailPopup('{{$template->uid}}');">
                                            <i class="fe-send"></i> Send Test Email
                                        </a>
                                    </li>
                                @if (Auth::user()->customer->can('delete', $template))
                                    <li class="user_fristicon"><a delete-confirm="{{ trans('messages.delete_templates_confirm') }}" href="{{ action('TemplateController@delete', ["uids" => $template->uid]) }}"><i class="fe-trash"></i> {{ trans("messages.delete") }}</a></li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $templates])
    <div class="modal fade" id="EmailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
    
    
    <script>
		var thumbPopup = new Popup();    
        var categoriesPopup = new Popup();           
    
        $('.upload-thumb-button').click(function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');
            
            thumbPopup.load(url);
        });

        $('.template-categories').click(function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');
            
            categoriesPopup.load(url);
        });
    </script>

@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.template_empty_line_1') }}
        </span>
    </div>
@endif
<script type="text/javascript">
    function showEmailPopup(uid){
        $("#templateUid").val(uid);
        $("#EmailModal").modal('show');
    }

    function sendTestEmail(){
        var testEmail = $("#TestEmail").val();
        var templateUid = $("#templateUid").val();
        var token = $("input[name=_token").val();

        if(testEmail == ''){
            swal.fire('Please enter test email');
        }else{
            $.ajax({
                url:"{{url('template/sendTestEmail')}}",
                type:"POST",
                data:{"testEmail":testEmail,"templateUid":templateUid,"_token":token},
                success:function(response){
                    swal.fire('Email Sent successfully');
                }
            })
        }
    }
</script>
