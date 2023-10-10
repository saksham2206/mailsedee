@if ($items->count() > 0)
    <table class="table pml-table table-striped"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($items as $key => $item)
            <tr>
                <td width="1%">
                    <div class="text-nowrap">
                        <div class="checkbox inline">
                            <label>
                                <input type="checkbox" class="node styled"
                                    name="ids[]"
                                    value="{{ $item->uid }}"
                                />
                            </label>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="server-avatar server-avatar-{{ $item->type }} mr-0">
                        <i class="icon-server"></i>
                    </span>
                </td>
                <td>
                    <h5 class="no-margin text-bold">
                        <a class="kq_search" href="@if($item->type != 'Gmail'){{ action('SendingServerController@edit', ["id" => $item->uid, "type" => $item->type]) }}@else javascript:void(0); @endif">{{ $item->name }}</a>
                    </h5>
                    <span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($item->created_at) }}</span>
                </td>
                <td>
                    <div class="single-stat-box pull-left ml-20">
                        <span class="no-margin stat-num kq_search">@if($item->type == 'Gmail')
                            {{$item->type}}
                            @else

                         {{ trans('messages.' . $item->type) }}

                            @endif</span>
                        <br />
                        <span class="text-muted">

                         {{ trans('messages.type') }}</span>
                    </div>
                </td>
                <td>
                    @if (!empty($item->host))
                        <div class="single-stat-box pull-left ml-20">
                            <span title="{{ $item->host }}" class="no-margin stat-num kq_search domain-truncate">{{ $item->host }}</span>
                            <br />
                            <span class="text-muted">{{ trans('messages.host') }}</span>
                        </div>
                    @elseif (!empty($item->aws_region))
                        <div class="single-stat-box pull-left ml-20">
                            <span title="{{ $item->aws_region }}" class="no-margin stat-num kq_search domain-truncate">{{ $item->aws_region }}</span>
                            <br />
                            <span class="text-muted">{{ trans('messages.aws_region') }}</span>
                        </div>
                    @elseif (!empty($item->domain))
                        <div class="single-stat-box pull-left ml-20">
                            <span title="{{ $item->domain }}" class="no-margin stat-num kq_search domain-truncate">{{ $item->domain }}</span>
                            <br />
                            <span class="text-muted">{{ trans('messages.domain') }}</span>
                        </div>
                    @endif
                </td>
                <td>
                    <select id="BounceHandler" class="form-control" onchange="changeBounce();" >
                        <option value="">Select Bounce Handler</option>
                        @php
                        $bounceHandels = getAllBounceHandler();
                        @endphp
                        @foreach($bounceHandels as $bounceHandel)
                        <option value="{{$bounceHandel->id}}" <?php echo $bounceHandel->id == $item->bounce_handler_id ? 'selected':'';?>  data-id="{{$item->uid}}">{{$bounceHandel->name}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <div class="single-stat-box pull-left ml-20">
                        <span class="text-muted"><strong>{{ number_with_delimiter($item->getSendingQuotaUsage()) }}</strong> {{ trans('messages.' . Acelle\Library\Tool::getPluralPrase('email', $item->getSendingQuotaUsage()) . '_quota') }}</span>
                        <br />
                        <span class="text-muted2">{{ trans('messages.sending_server.speed', ['limit' => $item->displayQuota()]) }}</span>
                    </div>
                </td>
                <td>
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-{{ $item->status }}">{{ trans('messages.sending_server_status_' . $item->status) }}</span>
                    </span>
                </td>
                <td class="text-right text-nowrap">
                    
                    @if (Auth::user()->customer->can('delete', $item) || Auth::user()->customer->can('disable', $item) || Auth::user()->customer->can('enable', $item))
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle bg-grey btn-icon" data-toggle="dropdown">Action<span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @if (Auth::user()->customer->can('update', $item) && $item->type != 'Gmail')
                                    <li>
                                        <a href="{{ action('SendingServerController@edit', ["id" => $item->uid, "type" => $item->type]) }}" data-popup="tooltip" title="{{ trans('messages.edit') }}" type="button" class=""><i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
                                    </li>
                                    
                                @endif
                                @if (Auth::user()->customer->can('enable', $item))
                                    <li>
                                        <a link-confirm="{{ trans('messages.enable_sending_servers_confirm') }}" href="{{ action('SendingServerController@enable', ["uids" => $item->uid]) }}">
                                            <i class="icon-checkbox-checked2"></i> {{ trans('messages.enable') }}
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->customer->can('disable', $item))
                                    <li>
                                        <a link-confirm="{{ trans('messages.disable_sending_servers_confirm') }}" href="{{ action('SendingServerController@disable', ["uids" => $item->uid]) }}">
                                            <i class="fa-solid fa-ban"></i> {{ trans('messages.disable') }}
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->customer->can('delete', $item))
                                    <li>
                                        <a delete-confirm="{{ trans('messages.delete_sending_servers_confirm') }}" href="{{ action('SendingServerController@delete', ["uids" => $item->uid]) }}">
                                            <i class="fe-trash"></i> {{ trans('messages.delete') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select')
    
@elseif (!empty(request()->keyword) || !empty(request()->filters["type"]))
    <div class="empty-list">
        <i class="icon-server"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-server"></i>
        <span class="line-1">
            {{ trans('messages.sending_server_empty_line_1') }}
        </span>
    </div>
@endif
<script type="text/javascript">
    function changeBounce(){
       //  var selected = $(this).find('option:selected');
       // var uid = selected.data('id');
       // var bounceId = $('#BounceHandler').val();
       var uid = '';
        var bounceId = '';
       $('#BounceHandler :selected').each(function(){
             uid = $(this).data('id');
        bounceId = $(this).val(); 
        });
        
        if(bounceId != ''){
            $.ajax({
                url : "{{url('bounce_handlers/updateServer')}}/"+uid+"/"+bounceId,
                //data:{'uid': uid,'id':bounceId},
                success:function(response){

                    window.location.reload();
                }
            });
        }
        
    }
</script>