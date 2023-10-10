@include('automation2._info')
				
@include('automation2._tabs', ['tab' => 'import'])
    
<!-- Dropzone -->
    <script type="text/javascript" src="{{ URL::asset('assets2/lib/dropzone/dropzone.js') }}"></script>
    <link href="{{ URL::asset('assets2/lib/dropzone/dropzone.css') }}" rel="stylesheet" type="text/css">
    
    

    <style>
      h3 {
        line-height: 30px;
        text-align: center;
      }
     
      #drop_file_area {
        height: 200px;
        border: 2px dashed #ccc;
        line-height: 200px;
        text-align: center;
        font-size: 20px;
        background: #f9f9f9;
        margin-bottom: 15px;
      }
     
      .drag_over {
        color: #000;
        border-color: #000;
      }
     
      .thumbnail {
        width: 100px;
        height: 100px;
        padding: 2px;
        margin: 2px;
        border: 2px solid lightgray;
        border-radius: 3px;
        float: left;
      }
     
      #upload_file {
        display: none;
      }

      .page-content {
            display: block;
        }
        .content-wrapper {
            display: block;
            vertical-align: top;
        }
    </style>
    
<div class="row">
        
        <div class="col-md-12">
            <form action="{{ url("automation/step3store") }}" method="POST" class="form-validate-jqueryz">
                {{ csrf_field() }}
                <!-- <h3>Contact</h3>
                <fieldset> -->
                <div>
                <p>Add your subscribers to begin your campaign. For reference, download our <a href=''>sample format</a>.</p>
                  <!-- Nav tabs -->
                  <ul class="nav nav-tabs mt-3 mb-4 upload_contact" role="tablist" id="tabdata">
                    <li role="presentation" class="nav-item active"><a href="#home" aria-controls="home" class="nav-link" role="tab" data-toggle="tab">Upload CSV</a></li>
                    <li role="presentation" class="nav-item"><a href="#profile" class="nav-link" aria-controls="profile" role="tab" data-toggle="tab">Manually Create </a></li>
                    <!-- <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Existing List</a></li> -->
                    
                  </ul>

                  <!-- Tab panes -->
                  <div class="tab-content">
                    <input type="file" name="files[]" id="contactFile" style="display: none;">
                    <div role="tabpanel" class="tab-pane active" id="home">
                        <div id="drop_file_area" onclick="openContact();">
                          Drag and Drop Files Here or Click here to select file
                          
                        </div>
                        <div id="uploaded_file" style="max-height: 300px; overflow:scroll;"></div>
                        <textarea id="wholecsvdata" style="display:none;"></textarea>

                        <div class="maping_data">
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Email Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="EmailField" id="EmailField">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Name Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="NameField" id="NameField">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="row">
                                <div class="col-sm-12">
                                    <label>Other Field Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="keyName" id="keyName">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                    <select class="HeaderColumn" name="valueName" id="valueName">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div> -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Company Column <i style="color: #ec0707;">*</i></label>
                                    <select class="HeaderColumn" name="CompanyField" id="CompanyField">
                                        <option value="">Select One Of Header</option>
                                    </select>
                                </div>
                            </div>                            
                        </div>
                        <a class="btn btn-success btntext" id="importContact" onclick="importContact();" style="display:none;">Import</a>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="profile">
                        <div class="ms-hint"> <i class="fa fa-question-circle" style="margin-right: 5px;"></i><span class="inner"> To include names with your email addresses enter them like so: <br><pre>"Jane Doe" &lt;jane@doe.com&gt; [company name]</pre></span></div>
                        <!-- <input type="text" name="list_name" id="list_name" class="form-control"> -->
                        <textarea name="list_data" id="list_data1" class="form-control"></textarea>
                        <a class="btn btn-success mt-2" id="importContactManualy" onclick="importContactManualy();">Import</a>

                    </div>
                    
                    
                    <input type="hidden" name="list_id" value="{{$automation->mail_list_id}}">
                    <input type="hidden" name="automation_uid" value="{{$automation->uid}}">
                    <input type="hidden" name="contactType" value="CSV" id="contactType">
                  </div>

                </div>
                
                 
                <!-- </fieldset> -->
            </form>
                
        </div>
    </div>

    <!-- tabs Script Start Here By Rajat -->
    <script type="text/javascript">
        // $(document).ready(function(){
            
        //     $('#tabdata li a:not(:first)').addClass('inactive');
        //     $('.container').hide();
        //     $('.container:first').show();
                
        //     $('#tabdata li a').click(function(){
        //         var t = $(this).attr('id');
        //       if($(this).hasClass('inactive')){ //this is the start of our condition 

        //         $('#tabdata li a').addClass('inactive');
        //         $(this).addClass('active');           
        //         $(this).removeClass('inactive');
                
        //         $('.container').hide();
        //         $('#'+ t + 'C').fadeIn('slow');
        //      }
        //     });
        // });
    </script>

    <!-- tabs Script End Here By Rajat -->
    <!-- Drag And Drop Script Start Here By Rajat -->
    
    <script>
      $(document).ready(function () {
        
        $(".HeaderColumn").change(function(){
            var btntext = "false";
            $(".HeaderColumn").each(function(){
                if($(this).val() == ''){
                    btntext = "true";
                }
            });
            if(btntext != "true"){
                $("#importContact").show();
            }
        });
        $(".maping_data").hide();
        $(".mail_list_box").change(function(){
            $("#contactType").val('select');
        });
        $("html").on("dragover", function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
     
        $("html").on("drop", function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
     
        $('#drop_file_area').on('dragover', function () {
          $(this).addClass('drag_over');
          return false;
        });
     
        $('#drop_file_area').on('dragleave', function () {
          $(this).removeClass('drag_over');
          return false;
        });
     
        $('#drop_file_area').on('drop', function (e) {
          e.preventDefault();
          $(this).removeClass('drag_over');
          var formData = new FormData();
          var files = e.originalEvent.dataTransfer.files;
          for (var i = 0; i < files.length; i++) {
            formData.append('file[]', files[i]);
          }
          var token = $("input[name=_token]").val();
          formData.append("_token",token)
          uploadFormData(formData);
        });
        

        $('#contactFile').change(function(){
            var formData = new FormData();
                var files = $('#contactFile')[0].files;
             // var files = $("input[name=files[]]").val();
              for (var i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]);
              }
              var token = $("input[name=_token]").val();
              formData.append("_token",token)
              uploadFormData(formData);
        });
     
        function uploadFormData(form_data) {
            var token = $("input[name=_token]").val();
          $.ajax({
            url: "{{url('automation/uploadCsv')}}",
            method: "POST",
            data: form_data,
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                var obj = JSON.parse(data);
                console.log(obj);
                if(obj.status == false){
                    swal.fire(obj.Msg);
                }else{
                    var sendData ="<table class='table table-stripe table-bordered'>";
                    var headerData;
                  //var files = e.originalEvent.dataTransfer.files;
                  for(var i = 0; i < obj.length; i++){
                    sendData += "<tr>";

                    for(var l= 0; l < obj[i]['header'].length; l++){
                        sendData += "<th>"+obj[i]['header'][l]+"</th>";
                        headerData +="<option value='"+obj[i]['header'][l]+"'>"+obj[i]['header'][l]+"</option>";
                    }
                    sendData += "</tr>";
                    var finalData = obj[i]['data'];
                    for (var j = 0; j < finalData.length; j++) {
                        //console.log(finalData);
                        sendData += "<tr>";
                        for (var k = 0; k < obj[i]['header'].length ; k++) {
                            var header = obj[i]['header'][k];
                            //headerArray = obj[i]['header']; 
                            sendData +="<td>"+finalData[j][header]+"</td>"; 
                            
                        }
                        sendData += "</tr>";
                    }
                }
                sendData += "</tr>";
              $("#wholecsvdata").text(data);
              $('#uploaded_file').append(sendData);
              $(".maping_data").show();
              $("#contactType").val('CSV');
              $('.HeaderColumn').append(headerData);
              

                
              }  
              
            }
          });
        }
      });
      function openContact(){
            $("#contactFile").trigger('click');
        }
      function importContact(){
        var EmailField = $("#EmailField").val();
        var NameField = $("#NameField").val();
        var keyName = $("#keyName").val();
        var valueName = $("#valueName").val();
        var CompanyField = $("#CompanyField").val();
        var wholecsvdata = $("#wholecsvdata").text();
        var from_name = $(".from_name").val();
        var sending_server = $(".mail_server_connect").val();
        var token = $("input[name=_token]").val();
        var list_id = $("input[name=list_id]").val();
        $.ajax({
            url:"{{url('automation/importContacts')}}",
            method:"POST",
            data : {"EmailField":EmailField,"NameField":NameField,"keyName":keyName,"valueName":valueName,"wholecsvdata":wholecsvdata,'list_id':list_id,"from_name":from_name,"sending_server":sending_server,"_token":token,'CompanyField':CompanyField},
            success:function(response){
                if(response.status == false){
                    swal.fire('Remove all invalid email ids and try import again !');
                }else{

                console.log(response);
                if(response.count >0 ){
                    swal.fire({
                    title: "Your subscriber have been successfully imported. "+response.count+" duplicate records skipped",
                    text: "",
                    confirmButtonColor: "#00695C",
                    type: "success",
                    allowOutsideClick: true,
                    confirmButtonText: "Ok",
                    customClass: "swl-success"
                });
                }else{
                    swal.fire({
                        title: "Your subscriber have been successfully imported.",
                        text: "",
                        confirmButtonColor: "#00695C",
                        type: "success",
                        allowOutsideClick: true,
                        confirmButtonText: "Ok",
                        customClass: "swl-success"
                    });
                }
                

                }
            }

        });



      }

      function importContactManualy(){
        var EmailField = "EMAIL";
        var NameField = "FIRST_NAME";
        var LastName = "LAST_NAME";
        var Company = 'COMPANY';
        var wholecsvdata = $("#list_data1").val();
        var wholecsvdatas = wholecsvdata.replace(/</g,'=');
        var wholecsvdatass = wholecsvdatas.replace(/>/g,'');
        //var wholecsvdatasss = wholecsvdatass.replace()
        //alert(wholecsvdata);
        $("#contactType").val('INPUT');
        var from_name = $(".from_name").val();
        var sending_server = $(".mail_server_connect").val();
        var token = $("input[name=_token]").val();
        var list_id = $("input[name=list_id]").val();
        $.ajax({
            url:"{{url('automation/importContactManualy')}}",
            method:"POST",
            data : {"EmailField":EmailField,"NameField":NameField,"LastName":LastName,"wholecsvdata":wholecsvdatass,'list_id':list_id,"from_name":from_name,"sending_server":sending_server,"_token":token,'Company':Company},
            success:function(response){
                console.log(response);
                if(response.count >0 ){
                    swal.fire({
                    title: "Your subscriber have been successfully imported. "+response.count+"duplicate records skkipped",
                    text: "",
                    confirmButtonColor: "#00695C",
                    type: "success",
                    allowOutsideClick: true,
                    confirmButtonText: "Ok",
                    customClass: "swl-success"
                });
                }else{
                    swal.fire({
                        title: "Your subscriber have been successfully imported.",
                        text: "",
                        confirmButtonColor: "#00695C",
                        type: "success",
                        allowOutsideClick: true,
                        confirmButtonText: "Ok",
                        customClass: "swl-success"
                    });
                }

            }

        });



      }
    </script>
    <!-- Drag And Drop Script End Here By Rajat --> 