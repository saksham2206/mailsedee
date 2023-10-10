@extends('layouts.frontend')

@section('title', trans('messages.create_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
	<script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="https://cdn.tiny.cloud/1/rnb832fakcjvuxhuesi1fsl4trkhfl30f8fz5yewgyjje1ik/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

@section('page_header')

    <div class="page-title listing-form">				
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("TemplateController@index") }}">{{ trans('messages.templates') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.template.new_template') }}</span>
        </h1>				
    </div>
    <hr>

@endsection

@section('content')
    
    <form action="{{ action('TemplateController@storeTemplate') }}" method="POST" class="template-form form-validate-jquery listing-form">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-6">

                <div class="sub_section">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => '',
                        'name' => 'name',
                        'value' => $template->name,
                        'label' => 'Enter your template\'s name here',
                        'help_class' => 'template',
                        'rules' => ['name' => 'required']
                    ])
                </div>
                <div class="sub_section">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => '',
                        'name' => 'subject',
                        'value' => '',
                        'label' => 'Your template\'s subject',
                        'help_class' => 'template',
                        'rules' => []
                    ])
                </div>
                <div class="sub_section">
                    @include('helpers.form_control', [
                        'type' => 'textarea',
                        'class' => '',
                        'name' => 'content',
                        'value' => '',
                        'label' => 'Your template\'s content',
                        'help_class' => 'template',
                        'rules' => []
                    ])
                </div>
        </div>
    </div>
        
    <div class="row">
        <div class="col-md-12" style="position: relative;">
			<div class="d-flex align-items-center mt-4 template-create-sticky">
				<div class="text-left">
					<button type="submit" class="btn bg-teal mr-10 start-design"><i class="icon-check"></i> Create Template</button>
				</div>
			</div>

        </div>
    </div>
    </form>
        
    <script>	
        $(document).ready(function() {
            $(document).on('click', '.select-template-layout', function() {
				var template = $(this).attr('data-template');
                
                // unselect all layouts
                $('.select-template-layout').removeClass('selected');
                
                // select this
                $(this).addClass('selected');

				// unselect all
				$('[name=template]').val('');
				
				// update template value
				if (typeof(template) !== 'undefined') {
					$('[name=template]').val(template);
				}
            });

            //CKEDITOR.replace( 'content' )

            });
            var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

          tinymce.init({
            selector: 'textarea',
            plugins: 'print preview powerpaste casechange importcss searchreplace autolink autosave save directionality advcode visualblocks visualchars fullscreen image link media mediaembed template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists checklist wordcount tinymcespellchecker a11ychecker imagetools textpattern noneditable help formatpainter permanentpen pageembed charmap tinycomments mentions quickbars linkchecker emoticons advtable export',
            tinydrive_token_provider: 'URL_TO_YOUR_TOKEN_PROVIDER',
            tinydrive_dropbox_app_key: 'YOUR_DROPBOX_APP_KEY',
            tinydrive_google_drive_key: 'YOUR_GOOGLE_DRIVE_KEY',
            tinydrive_google_drive_client_id: 'YOUR_GOOGLE_DRIVE_CLIENT_ID',
            mobile: {
              plugins: 'print preview powerpaste casechange importcss tinydrive searchreplace autolink autosave save directionality advcode visualblocks visualchars fullscreen image link media mediaembed template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists checklist wordcount tinymcespellchecker a11ychecker textpattern noneditable help formatpainter pageembed charmap mentions quickbars linkchecker emoticons advtable'
            },
            menu: {
              tc: {
                title: 'Comments',
                items: 'addcomment showcomments deleteallconversations'
              }
            },
            menubar: 'file edit view insert format tools table',
            toolbar: 'undo redo | bold italic underline strikethrough | mybutton fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist checklist | forecolor backcolor casechange permanentpen formatpainter removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media pageembed template link anchor codesample | a11ycheck ltr rtl | showcomments addcomment',
            autosave_ask_before_unload: true,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',
            image_advtab: true,
            link_list: [
              { title: 'My page 1', value: 'https://www.tiny.cloud' },
              { title: 'My page 2', value: 'http://www.moxiecode.com' }
            ],
            image_list: [
              { title: 'My page 1', value: 'https://www.tiny.cloud' },
              { title: 'My page 2', value: 'http://www.moxiecode.com' }
            ],
            image_class_list: [
              { title: 'None', value: '' },
              { title: 'Some class', value: 'class-name' }
            ],
            importcss_append: true,
            templates: [
                  { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
              { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
              { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
            ],
            template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
            template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
            height: 600,
            image_caption: true,
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            noneditable_noneditable_class: 'mceNonEditable',
            toolbar_mode: 'sliding',
            spellchecker_ignore_list: ['Ephox', 'Moxiecode'],
            tinycomments_mode: 'embedded',
            content_style: '.mymention{ color: gray; }',
            contextmenu: 'link image imagetools table configurepermanentpen',
            a11y_advanced_options: true,
            skin: useDarkMode ? 'oxide-dark' : 'oxide',
            content_css: useDarkMode ? 'dark' : 'default',
            /*
            The following settings require more configuration than shown here.
            For information on configuring the mentions plugin, see:
            https://www.tiny.cloud/docs/plugins/premium/mentions/.
            */
            
            setup: function (editor) {
              /* Menu items are recreated when the menu is closed and opened, so we need
                 a variable to store the toggle menu item state. */
              var toggleState = false;

              /* example, adding a toolbar menu button */
              editor.ui.registry.addMenuButton('mybutton', {
                text: 'Insert',
                fetch: function (callback) {
                  var items = [
                    
                    
                    
                  ];
                  callback(items);
                }
              });

            },
          });
    </script>
    
@endsection
