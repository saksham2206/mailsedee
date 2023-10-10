<script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
<div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Email</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <form  method="POST"  action="javascript:void(0);" id="templateForm" class="template-form form-validate-jquery">
          {{ csrf_field() }}
            <div class="row">
                <div class="col-md-12">
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
                        <div class="form-group col-md-12" style="padding: 0px !important;">
                          <label>Template</label>
                          <select class="form-control" id="templateSelect" name="Templates">
                            <option val="">Select Template</option>
                            @foreach($templates as $templateData)
                            <option value="{{$templateData->id}}"> {{$templateData->name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                        <div class="sub_section">
                            @include('helpers.form_control', [
                                'type' => 'text',
                                'class' => '',
                                'name' => 'subject',
                                'value' => $template->subject,
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
                                'value' => $template->content,
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
                            <input type="hidden" name="template_id" value="{{$template->id}}" id="template_id">
                            <input type="hidden" name="email_id" value="{{$email_id}}">
                            <center>
        					<button type="submit" class="btn btn-success bg-teal mr-10 start-design" onclick="updateTemplateForm();"><i class="icon-check"></i> Update Template</button>
                        </center>
        				</div>
        			</div>

                </div>
            </div>
    </form>
        
    <script>	
        $(document).ready(function() {
          setTimeout(function(){ 
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
                    @foreach($tags as $tag)
              
                    {
                      type: 'menuitem',
                      text: '{SUBSCRIBER_{{$tag->tag}}}',
                      onAction: function () {
                        editor.insertContent('{SUBSCRIBER_{{$tag->tag}}}');
                      }
                    },
                    @endforeach
                    
                    
                  ];
                  callback(items);
                }
              });

            },
          });

          
        //     $(document).on('click', '.select-template-layout', function() {
        // var template = $(this).attr('data-template');
                
        //         // unselect all layouts
        //         $('.select-template-layout').removeClass('selected');
                
        //         // select this
        //         $(this).addClass('selected');

        // // unselect all
        // $('[name=template]').val('');
        
        // // update template value
        // if (typeof(template) !== 'undefined') {
        //   $('[name=template]').val(template);
        // }
        //     });
            //setTimeout(function(){ alert("Hello"); 
              
              // }, 1000);
    //           setTimeout(function(){ 
    //             //alert('ji');
    //   CKEDITOR.replace( 'content' )
     },1000);
            
              $("#templateSelect").change(function(){
                var templateId = $("#templateSelect").val();
                if(templateId != ''){
                  $.ajax({
                    "url": "{{url('automation/tempalateSelect')}}/"+templateId,
                    success:function(response){
                      var obj = JSON.parse(response);
                      $(".name").val(obj.name);
                      $(".subject").val(obj.subject);
                      tinymce.activeEditor.setContent(obj.content);


                      CKEDITOR.instances.content.setData(obj.content)
                      //$(".contents").text(obj.content);
                    }

                  });
                }
              });
            });
            
    </script>
    
