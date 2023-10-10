@if ($templates->count() > 0)
    <div id="layout" class="tab-pane fade in active template-boxes layout mt-20" style="
        margin-left: -20px;
        margin-right: -20px;
    ">
        @foreach ($templates as $key => $template)
            <div class="col-xxs-12 col-xs-6 col-sm-3 col-md-2">
                <a 
                    href="{{ action('CampaignController@templateLayout', ['uid' => $campaign->uid, 'template' => $template->uid]) }}"
                    class="choose-theme"
                >
                    <div class="panel panel-flat">
                        <div class="panel-body">
                            <div>
                                <div class="panel-template-placeholder">
                                    <img src="{{ $template->getThumbUrl() }}?v={{ rand(0,10) }}" />
                                </div>
                                <label class="mb-20 text-center">{{ $template->name }}</label>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
        
    <hr style="clear: both">
        
    @include('elements/_per_page_select', ["items" => $templates])
    

    <script>
        var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            window.location = '{{ action('CampaignController@template', $campaign->uid) }}';
        }});
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
            {{ trans('messages.no_template_available') }}
        </span>
    </div>
@endif
