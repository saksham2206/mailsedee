@if ($templates->count() > 0)
    <div id="layout" class="tab-pane fade in active template-boxes mt-20" style="
        margin-left: -20px;
        margin-right: -20px;
    ">
        @foreach ($templates as $key => $template)
            <div class="col-xxs-12 col-xs-6 col-sm-3 col-md-2">
                <a href="javascript:;" class="select-template-layout" data-template="{{ $template->uid }}">
                    <div class="panel panel-flat panel-template-style">
                        <div class="panel-body">
                            <div class="panel-template-placeholder">
                                <img src="{{ $template->getThumbUrl() }}?v={{ rand(0,10) }}" />
                            </div>
                            <label class="mb-20 text-center">{{ $template->name }}</label>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
        
    <div style="clear:both">
        @include('elements/_per_page_select', ["items" => $templates])
    </div>
    
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
