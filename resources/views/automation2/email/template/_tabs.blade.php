<ul class="nav nav-pills email-template-tabs" id="pills-tab" role="tablist">
    @foreach (Acelle\Model\TemplateCategory::all() as $cat)
        <li class="nav-item">
            <a class="nav-link {{ isset($category) && $category->uid == $cat->uid ? 'active' : '' }}" href="javascript:;" data-href="{{ action('Automation2Controller@templateLayout', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
                'category_uid' => $cat->uid,
            ]) }}">
                {{ $cat->name }}
            </a>
        </li>
    @endforeach
    <li class="nav-item">
        <a class="choose-template-tab nav-link {{ actionName() == 'templateUpload' ? 'active' : '' }}" href="javascript:;" data-href="{{ action('Automation2Controller@templateUpload', [
            'uid' => $automation->uid,
            'email_uid' => $email->uid,
        ]) }}">Upload</a></li>
</ul>

<script>
    $('.email-template-tabs .nav-link').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('data-href');
        
        popup.load(url);
    });
</script>