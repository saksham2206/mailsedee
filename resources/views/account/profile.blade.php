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

    <!-- {{ $user->getProfileImageUrl() }} -->

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
                        'id' =>'footer_text',
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

  /*  $(document).ready(function() {
        setInterval("changeSelectColor()", 100);
        setTimeout(function(){ 
                //alert('ji');
      CKEDITOR.replace( 'footer_text' )
    },1000);
    });*/
</script>
<!-- <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
<script src="https://cdn.tiny.cloud/1/py4dnsnefql7ku3qv6gx8odmhsnbo8en42g7sp1a8gxm65br/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script type="text/javascript">
 tinymce.init({
  selector: '.setting-editors',
  
   image_class_list: [
            {title: 'img-responsive', value: 'img-responsive'},
            ],
            height: 500,
            setup: function (editor) {
                editor.on('init change', function () {
                    editor.save();
                });
            },
  plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',

            image_title: true,
            automatic_uploads: true,
            images_upload_url: '{{url("automation/upload")}}',
            file_picker_types: 'image',
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];

                    var reader = new FileReader();
                    reader.readAsDataURL(file);
                    // reader.onload = function () {
                    //     var id = 'blobid' + (new Date()).getTime();
                    //     var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    //     var base64 = reader.result.split(',')[1];
                    //     var blobInfo = blobCache.create(id, file, base64);
                    //     blobCache.add(blobInfo);
                    //     cb(blobInfo.blobUri(), { title: file.name });
                    // };
                };
                input.click();
            },
  //imagetools_cors_hosts: ['picsum.photos'],
  menubar: 'file edit view insert format tools table help',
  toolbar: 'undo redo | bold italic underline strikethrough |  mybutton  fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
  toolbar_sticky: true,
  autosave_ask_before_unload: true,
  autosave_interval: "30s",
  //autosave_prefix: "{path}{query}-{id}-",
  autosave_restore_when_empty: false,
  autosave_retention: "2m",
  image_advtab: true,
  remove_script_host: false,
  allow_script_urls: true,
  content_css: '//www.tiny.cloud/css/codepen.min.css',
  link_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Some class', value: 'class-name' }
  ],
  importcss_append: true,
  file_picker_callback: function (callback, value, meta) {
    /* Provide file and text for the link dialog */
    if (meta.filetype === 'file') {
      callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
    }

    /* Provide image and alt text for the image dialog */
    if (meta.filetype === 'image') {
        
      callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
    }

    /* Provide alternative source and posted for the media dialog */
    if (meta.filetype === 'media') {
      callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
    }
  },
  templates: [
        { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
    { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
    { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
  ],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
  height: 520,
  image_caption: true,
  quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
  noneditable_noneditable_class: "mceNonEditable",
  toolbar_mode: 'sliding',
  contextmenu: "link image imagetools table",
  setup: function (editor) {
              /* Menu items are recreated when the menu is closed and opened, so we need
                 a variable to store the toggle menu item state. */
              var toggleState = false;

              /* example, adding a toolbar menu button */
              editor.ui.registry.addMenuButton('mybutton', {
                text: 'Insert',
                fetch: function (callback) {
                  var items = [
                    
              
                    {
                      type: 'menuitem',
                      text: '{UNSUBSCRIBE_URL}',
                      onAction: function () {
                        editor.insertContent('{UNSUBSCRIBE_URL}');
                      }
                    },
                    
                    
                  ];
                  callback(items);
                }
              });

            },
 });

</script>
@endsection
