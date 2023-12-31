@extends('layouts.frontend')

@section('title', trans('messages.my_profile'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li class="active">{{ trans('messages.profile') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-profile"></i> {{ $user->displayName() }}</span>
        </h1>
    </div>

@endsection

@section('content')

    @include("account._menu")

    <form enctype="multipart/form-data" action="{{ action('AccountController@profile') }}" method="POST" class="form-validate-jqueryz">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-md-3">
                <div class="sub_section">
                    <h2 class="text-semibold text-teal-800">{{ trans('messages.profile_photo') }}</h2>
                    <div class="media profile-image">
                        <div class="media-left">
                            <a href="#" class="upload-media-container">
                                <img preview-for="image" empty-src="{{ URL::asset('assets/images/placeholder.jpg') }}" src="{{ $user->getProfileImageUrl() }}" class="img-circle" alt="">
                            </a>
                            <input type="file" name="image" class="file-styled previewable hide">
                            <input type="hidden" name="_remove_image" value='' />
                        </div>
                        <div class="media-body text-center">
                            <h5 class="media-heading text-semibold">{{ trans('messages.upload_your_photo') }}</h5>
                            {{ trans('messages.photo_at_least', ["size" => "300px x 300px"]) }}
                            <br /><br />
                            <a href="#upload" onclick="$('input[name=image]').trigger('click')" class="btn btn-xs bg-teal mr-10"><i class="icon-upload4"></i> {{ trans('messages.upload') }}</a>
                            <a href="#remove" class="btn btn-xs bg-grey-800 remove-profile-image"><i class="icon-trash"></i> {{ trans('messages.remove') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="sub_section">
                    <h2 class="text-semibold text-teal-800">{{ trans('messages.basic_information') }}</h2>

                    <div class="row">
                        <div class="col-md-6">
                            @include('helpers.form_control', ['type' => 'text', 'name' => 'first_name', 'value' => $user->first_name, 'rules' => $user->rules()])
                        </div>
                        <div class="col-md-6">
                            @include('helpers.form_control', ['type' => 'text', 'name' => 'last_name', 'value' => $user->last_name, 'rules' => $user->rules()])
                        </div>
                    </div>

                    @include('helpers.form_control', ['type' => 'select', 'name' => 'timezone', 'value' => $customer->timezone, 'options' => Tool::getTimezoneSelectOptions(), 'include_blank' => trans('messages.choose'), 'rules' => $user->rules()])

                    @include('helpers.form_control', ['type' => 'select', 'name' => 'language_id', 'label' => trans('messages.language'), 'value' => $customer->language_id, 'options' => Acelle\Model\Language::getSelectOptions(), 'include_blank' => trans('messages.choose'), 'rules' => $user->rules()])

                    <div class="row">
                        <div class="col-md-6 color-box">
                            @include('helpers.form_control', [
                                'type' => 'select',
                                'class' => '',
                                'name' => 'color_scheme',
                                'value' => $customer->color_scheme,
                                'help_class' => 'customer',
                                'options' => Acelle\Model\Customer::colors("color_scheme"),
                                'rules' => '',
                            ])
                        </div>
                        <div class="col-md-6 color-box">
                            @include('helpers.form_control', [
                                'type' => 'select',
                                'class' => '',
                                'name' => 'text_direction',
                                'value' => $customer->text_direction,
                                'help_class' => 'customer',
                                'options' => [
                                    ['text' => trans('messages.text_direction.ltr'), 'value' => 'ltr'],
                                    ['text' => trans('messages.text_direction.rtl'), 'value' => 'rtl']
                                ],
                                'rules' => '',
                            ])
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-4">
                <div class="sub_section">
                    <h2 class="text-semibold text-teal-800">{{ trans('messages.account') }}</h2>

                    @include('helpers.form_control', ['type' => 'text', 'name' => 'email', 'value' => $customer->user->email, 'help_class' => 'profile', 'rules' => $user->rules()])

                    @include('helpers.form_control', ['type' => 'password', 'label'=> trans('messages.new_password'), 'name' => 'password', 'rules' => $user->rules()])

                    @include('helpers.form_control', ['type' => 'password', 'name' => 'password_confirmation', 'rules' => $user->rules()])

                </div>
            </div>
        </div>

        <div class="text-right">
            <button class="btn bg-teal"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
        </div>

    </form>
    <form action="{{url('automation/addSignature')}}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="form-grouo">
                    @include('helpers.form_control', [
                        'class' => 'setting-editors',
                        'type' => 'textarea',
                        'name' => 'footer_text',
                        'label' => 'Compose Signature',
                        'value' => $customer->footer_text,
                        'help_class' => 'plan',
                    ])
                </div>
                <div class="text-right">
                    <button class="btn bg-teal"><i class="icon-check"></i> Save Footer</button>
                </div>
            </div>
        </div>
    </form>

<script>
    function changeSelectColor() {
        $('.select2 .select2-selection__rendered, .select2-results__option').each(function() {
            var text = $(this).html();
            if (text == '{{ trans('messages.default') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-teal-600'></i>");
                }
            }
            if (text == '{{ trans('messages.blue') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-blue'></i>");
                }
            }
            if (text == '{{ trans('messages.green') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-green'></i>");
                }
            }
            if (text == '{{ trans('messages.brown') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-brown'></i>");
                }
            }
            if (text == '{{ trans('messages.pink') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-pink'></i>");
                }
            }
            if (text == '{{ trans('messages.grey') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-grey'></i>");
                }
            }
            if (text == '{{ trans('messages.white') }}') {
                if($(this).find("i").length == 0) {
                    $(this).prepend("<i class='icon-square text-white'></i>");
                }
            }
        });
    }

    $(document).ready(function() {
        setInterval("changeSelectColor()", 100);
        setTimeout(function(){ 
                //alert('ji');
      CKEDITOR.replace( 'footer_text' )
    },1000);
    });
</script>

@endsection
