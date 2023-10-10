<div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Change Time</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="changeForm">
          {{ csrf_field() }}
          <div class="form-group col-md-12">
            <label>Amount</label>
            <input class="form-control" type="text" name="day" value="{{$day}}">
          </div>
          <div class="col-md-12">
            @php
            if(trim($unit,' ') == 'days'){
              $unit = 'day';
            }
            @endphp
            @include('helpers.form_control', ['type' => 'select',
                                'name' => 'unit',
                                'value' => trim($unit,' '),
                                'options' => Acelle\Model\Automation2::waitTimeUnitOptions(),
                                'help_class' => 'plan',
                                'class' =>'form-control',
                            ])
          </div>
          <div class="col-md-12">
            <center>
              <input type="hidden" name="uid" value="{{$automation->uid}}">
              <input type="hidden" name="key" value="{{$key}}">
              <button type="button" class="btn btn-success" onclick="saveData();">Save</button>
            </center>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    function saveData(){
      var changeFormData = $("#changeForm").serialize();
      $.ajax({
        url:"{{url('automation/updateWait')}}",
        method:"POST",
        data: changeFormData,
        success:function(response){
          window.location.reload();
        }
      })
    }
  </script>