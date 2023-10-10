<link rel="stylesheet" href="{{ URL::asset('assets/datepicker/dist/datepicker.min.css') }}">
<link rel="stylesheet" href="{{ URL::asset('assets/timepicker/jquery.timepicker.css') }}">
<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.date.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assets/datepicker/dist/datepicker.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::asset('assets/timepicker/jquery.timepicker.js') }}"></script>
<div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Start Time</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
         <form  method="POST" id="templateForm" action="{{url('automation/updateTime')}}" class="template-form form-validate-jquery">
          @csrf
          <div class="mb-20 m-30 time_modal">
           
            <input type="hidden" name="uid" value="{{$automation->uid}}">
            @include('helpers.form_control', [
                'type' => 'date2',
                'class' => 'datepickers',
                'label' => trans('messages.automation.date'),
                'name' => 'options[date]',
                'value' => ($trigger->getOption('date') ? $trigger->getOption('date') : toDateString(\Carbon\Carbon::now())),
                'help_class' => 'trigger',
                'rules' => $rules,
            ])
            
            @include('helpers.form_control', [
                'type' => 'time2',
                'name' => 'options[at]',
                'label' => trans('messages.automation.at'),
                'value' => ($trigger->getOption('at') ? $trigger->getOption('at') : toTimeString(\Carbon\Carbon::now())),
                'rules' => $rules,
                'class' => 'timepickers',
                'help_class' => 'trigger'
            ])
        </div>
        <input type="submit" name="submit" value="Update" class="btn btn-success" style="float: right;  margin-right: 30px;">
           </form>
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> -->
    </div>
  </div>
  <script type="text/javascript">
    $(document).ready(function(){
      $('.datepickers').datepicker({
        'format' : 'yyyy-mm-dd',
      });
      $('input.timepickers').timepicker({});
    });
  </script>