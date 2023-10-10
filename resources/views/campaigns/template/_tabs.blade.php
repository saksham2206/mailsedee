<ul class="nav nav-tabs mc-nav mb-3" id="pills-tab" role="tablist">
    @foreach (Acelle\Model\TemplateCategory::all() as $cat)
        <li class="nav-item {{ isset($category) && $category->uid == $cat->uid ? 'active' : '' }}">
            <a class="choose-template-tab" href="{{ action('CampaignController@templateLayout', [
                'uid' => $campaign->uid,
                'category_uid' => $cat->uid,
            ]) }}">
                {{ $cat->name }}
            </a>
        </li>
    @endforeach
    <li class="nav-item {{ actionName() == 'templateUpload' ? 'active' : '' }}"><a class="choose-template-tab nav-link" href="{{ action('CampaignController@templateUpload', $campaign->uid) }}">Upload</a></li>
</ul>