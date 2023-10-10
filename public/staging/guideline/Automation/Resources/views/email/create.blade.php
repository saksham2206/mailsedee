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
         <form  method="POST" id="templateForm" class="template-form form-validate-jquery">
          {{ csrf_field() }}
          <div class="row">
              <div class="col-md-12">

                      <div class="sub_section">
                          @include('helpers.form_control', [
                              'type' => 'text',
                              'class' => '',
                              'name' => 'name',
                              'value' => '',
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
                          <input type="hidden" name="type" value="{{$type}}">
                          <input type="hidden" name="sequenceId" value="{{$sequenceId}}">
                      </div>
              </div>
          </div>
              
          <div class="row">
              <div class="col-md-12" style="position: relative;">
            <div class="d-flex align-items-center mt-4 template-create-sticky">
              <div class="text-left">
                <a href="javascript:void(0);" onclick="submitTemplateForm();" class="btn bg-teal mr-10 start-design"><i class="icon-check"></i> Create Template</a>
              </div>
            </div>

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
        // $(document).ready(function() {
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
              CKEDITOR.replace( 'content' )
              // }, 1000);
            

            //});
            
    </script>