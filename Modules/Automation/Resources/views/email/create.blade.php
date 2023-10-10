 <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
 <script src="https://cdn.tiny.cloud/1/py4dnsnefql7ku3qv6gx8odmhsnbo8en42g7sp1a8gxm65br/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
 <style type="text/css">
   .modal-dialog {
  max-width: 767px;
}
 </style>
 <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Email</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <form  method="POST" id="templateForm" class="template-form form-validate-jquery">
          {{ csrf_field() }}
          <div class="row">
              <div class="col-md-12">

                      <div class="sub_section">
                          @include('helpers.form_control', [
                              'type' => 'text',
                              'class' => 'name',
                              'name' => 'name',
                              'value' => '',
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
                            @foreach($template as $templateData)
                            <option value="{{$templateData->id}}"> {{$templateData->name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="sub_section">
                          @include('helpers.form_control', [
                              'type' => 'text',
                              'class' => 'subject',
                              'name' => 'subject',
                              'value' => '',
                              'label' => 'Your template\'s subject',
                              'help_class' => 'template',
                              'rules' => ['subject' => 'required']
                          ])
                      </div>
                      <div class="sub_section">
                          @include('helpers.form_control', [
                              'type' => 'textarea',
                              'class' => 'contents',
                              'name' => 'content',
                              'id' => 'contents',
                              'value' => '',
                              'label' => 'Your template\'s content',
                              'help_class' => 'template',
                              'rules' => ['content' => 'required']
                          ])
                          <input type="hidden" name="type" value="{{$type}}">
                          <input type="hidden" name="sequenceId" value="{{$sequenceId}}">
                          <input type="hidden" name="uid" value="{{$uid}}">
                          <input type="hidden" name="clickType" value="{{$clickType}}">
                          <input type="hidden" name="elementId" value="{{$elementId}}">
                      </div>
              </div>
          </div>
          <!-- <div class="row" style="padding-bottom: 20px !important;">
            <div class="col-sm-12">
              @foreach($tags as $tag)
              {SUBSCRIBER_{{$tag->tag}}} ,
              @endforeach
            </div>
          </div>  -->
          <div class="row" style="display: flex !important;">
              <div class="col-md-12 " >
              <center>
                <a href="javascript:void(0);" id="createBuutons" onclick="submitTemplateForm();" class="btn btn-success bg-teal mr-10 start-design"><i class="icon-check"></i> Create Template</a>
                <a href="javascript:void(0);" id="UpdateBuutons" onclick="submitTemplateForm();" class="btn btn-success bg-teal mr-10 start-design hide"><i class="icon-check"></i> Use Template</a>
             </center>
              </div>
          </div>
          </form>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
    </div>
  </div>

  <script> 
    
        $(document).ready(function() {
          setTimeout(function(){ 
            var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

          tinymce.init({
            selector: 'textarea',
            plugins: 'print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help  charmap  quickbars emoticons ',
            
            mobile: {
              plugins: 'print preview  importcss tinydrive searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons '
            },
            menu: {
              tc: {
                title: 'Comments',
                items: 'addcomment showcomments deleteallconversations'
              }
            },
            menubar: 'file edit view insert format tools table',
            toolbar: 'undo redo | bold italic underline strikethrough | mybutton fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl ',
            autosave_ask_before_unload: true,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',
            image_advtab: true,
            
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
            //noneditable_noneditable_class: 'mceNonEditable',
            toolbar_mode: 'sliding',
            
            content_style: '.mymention{ color: gray; }',
            //contextmenu: 'link image imagetools table configurepermanentpen',
            
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
    //       $(document).on('focusin', function(e) {
    //         console.log($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root, .tox-dialog").length);
    //     if($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root, .tox-dialog").length){e.stopImmediatePropagation()}
    // });


          
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
                if(templateId != 'Select Template'){
                  $.ajax({
                    "url": "{{url('automation/tempalateSelect')}}/"+templateId,
                    success:function(response){
                      var obj = JSON.parse(response);
                      $(".name").val(obj.name);
                      $(".subject").val(obj.subject);
                      $("#createBuutons").hide();
                      $("#UpdateBuutons").show();
                      tinymce.activeEditor.setContent(obj.content);


                      //CKEDITOR.instances.content.setData(obj.content)
                      //$(".contents").text(obj.content);
                    }

                  });
                }else{
                  $(".name").val('');
                      $(".subject").val('');
                      $("#createBuutons").show();
                      $("#UpdateBuutons").hide();
                      tinymce.activeEditor.setContent('');
                }
              });
            });
            
    </script>