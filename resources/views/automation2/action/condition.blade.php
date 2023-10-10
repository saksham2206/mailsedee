@php
    $options = [
        ['text' => trans('messages.automation.condition.open'), 'value' => 'open'],
        //['text' => trans('messages.automation.condition.click'), 'value' => 'click'],
    ];

    $trigger = $automation->getTrigger();

    if ($trigger->getOption('type') == 'woo-abandoned-cart') {
        $options = array_merge($options, [
            ['text' => trans('messages.automation.condition.cart_buy_anything'), 'value' => 'cart_buy_anything'],
            ['text' => trans('messages.automation.condition.cart_buy_item'), 'value' => 'cart_buy_item'],
        ]);
    }
@endphp



<div class="condition-type">
    
    <div class="condition-setting">
    </div>
</div>
    
<script>

    function showSetting(container) {
        var box = new Box(container.find('.condition-setting'));
        var type = 'open';
        var url = '{{ action('Automation2Controller@conditionSetting', [
        'uid' => $automation->uid,
    ]) }}?type=' + type + '&element_id={{ $element->get('id') }}';

        box.load(url);
    }

    // Toggle condition options
    $(document).on('change', '.condition-type [name=type]', function() {
        showSetting($(this).closest('.condition-type'));
    });
    
    $('.condition-type').each(function() {
        showSetting($(this));
    });
</script>