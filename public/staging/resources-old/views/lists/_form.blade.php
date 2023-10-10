<div class="sub_section">
    <h2 class="text-semibold">{{ trans('messages.list.title.edit') }}</h2>
    <h3 class="text-semibold">{{ trans('messages.list_details') }}
    </h3>

    <div class="row">
        <div class="col-md-6">
                @include('helpers.form_control', ['type' => 'text', 'name' => 'name', 'value' => $list->name, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <!-- <div class="col-md-6">
            <div class="hiddable-cond" data-control="[name=use_default_sending_server_from_email]" data-hide-value="1">
                @include('helpers.form_control', [
                    'type' => 'autofill',
                    'id' => 'sender_from_input',
                    'name' => 'from_email',
                    'label' => trans('messages.from_email'),
                    'value' => $list->from_email,
                    'help_class' => 'list',
                    'rules' => Acelle\Model\MailList::$rules,
                    'url' => action('SenderController@dropbox'),
                    'empty' => trans('messages.sender.dropbox.empty'),
                    'error' => trans('messages.sender.dropbox.error', [
                        'sender_link' => action('SenderController@index'),
                    ]),
                    'header' => trans('messages.verified_senders'),
                ])
            </div>
        </div> -->
    </div>
    <!-- <div class="row">
        <div class="col-md-6">
                @include('helpers.form_control', ['type' => 'text', 'name' => 'from_name', 'label' => trans('messages.default_from_name'), 'value' => $list->from_name, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <div class="col-md-6">
                @include('helpers.form_control', ['type' => 'text', 'name' => 'default_subject', 'label' => trans('messages.default_email_subject'), 'value' => $list->default_subject, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
    </div> -->
</div>

<!-- <div class="sub_section">
    <h3 class="text-semibold">
        {{ trans('messages.contact_information') }}
        <span class="subhead">{!! trans('messages.default_from_your_contact_information', ['link' => action('AccountController@contact')]) !!}</span>
    </h3>
    <div class="row">
        <div class="col-md-6">
                @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[company]', 'label' => trans('messages.company_organization'), 'value' => $list->contact->company, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <div class="col-md-6">
                @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[state]', 'label' => trans('messages.state_province_region'), 'value' => $list->contact->state, 'rules' => Acelle\Model\MailList::$rules])
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[address_1]', 'label' => trans('messages.address_1'), 'value' => $list->contact->address_1, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[city]', 'label' => trans('messages.city'), 'value' => $list->contact->city, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[address_2]', 'label' => trans('messages.address_2'), 'value' => $list->contact->address_2, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[zip]', 'label' => trans('messages.zip_postal_code'), 'value' => $list->contact->zip, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'select', 'name' => 'contact[country_id]', 'label' => trans('messages.country'), 'value' => $list->contact->country_id, 'options' => Acelle\Model\Country::getSelectOptions(), 'include_blank' => trans('messages.choose'), 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[phone]', 'label' => trans('messages.phone'), 'value' => $list->contact->phone, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[email]', 'label' => trans('messages.email'), 'value' => $list->contact->email, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
        <div class="col-md-6">
            @include('helpers.form_control', ['type' => 'text', 'name' => 'contact[url]', 'label' => trans('messages.url'), 'label' => trans('messages.home_page'), 'value' => $list->contact->url, 'help_class' => 'list', 'rules' => Acelle\Model\MailList::$rules])
        </div>
    </div>
</div> -->





<script>
    
</script>
