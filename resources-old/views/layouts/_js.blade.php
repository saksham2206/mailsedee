    <!-- Core JS files -->
	
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
	<!-- <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery.min.js') }}"></script> -->
	<!-- <script src="{{ URL::asset('assetsnew/js/vendor.min.js') }}"></script> -->
	<script
  src="https://code.jquery.com/jquery-2.2.4.min.js"
  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
  crossorigin="anonymous"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/loaders/pace.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/loaders/blockui.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/nicescroll.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/drilldown.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/selects/select2.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/validation/validate.min.js') }}"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/d3/d3.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/d3/d3_tooltip.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switchery.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/selects/bootstrap_multiselect.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/ui/moment/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/daterangepicker.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/notifications/bootbox.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/notifications/sweet_alert.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switchery.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/switch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/pickadate/picker.date.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/jquery.numeric.min.js') }}"></script>

    <link rel="stylesheet" href="{{ URL::asset('js/scrollbar/jquery.mCustomScrollbar.css') }}">
    <script type="text/javascript" src="{{ URL::asset('js/scrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/jquery.cookie.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('rangeslider/bootstrap-slider.js') }}"></script>
	
	<script type="text/javascript" src="{{ URL::asset('bootstrap3-editable/js/bootstrap-editable.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/tooltipster/js/tooltipster.bundle.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/timepicker/jquery.timepicker.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/datepicker/dist/datepicker.min.js') }}"></script>
	<!-- Tooltip -->
	<link rel="stylesheet" href="{{ URL::asset('assets/tooltipster/css/tooltipster.bundle.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('assets/tooltipster/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css') }}">
	<script type="text/javascript" src="{{ URL::asset('assets/tooltipster/js/tooltipster.bundle.min.js') }}"></script>		

		
	
	<!-- Select2 -->
	
	
	<!-- PNotify -->
	<link href="{{ URL::asset('assets2/lib/pnotify-4.0.0/PNotifyBrightTheme.css') }}" rel="stylesheet" type="text/css">
	<script src="{{ URL::asset('assets2/lib/pnotify-4.0.0/iife/PNotify.js') }}"></script>
	<script src="{{ URL::asset('assets2/lib/pnotify-4.0.0/iife/PNotifyButtons.js') }}"></script>
	<script src="{{ URL::asset('assets2/lib/nonblockjs/NonBlock.js') }}"></script>

       

        <!-- Vendor js -->
        

        <!-- Plugins js-->
        <script src="{{ URL::asset('assetsnew/libs/flatpickr/flatpickr.min.js') }}"></script>
        <!-- <script src="{{ URL::asset('assetsnew/libs/apexcharts/apexcharts.min.js') }}"></script> -->


        <!-- Dashboar 1 init js-->
        <script src="{{ URL::asset('assetsnew/js/pages/dashboard-1.init.js') }}"></script> 

        <!-- App js-->
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/node-waves/0.7.6/waves.js" integrity="sha512-ki3BIF4wb/lCj832HaJfMWid+jCNyET3ygUheQrYW6i/JJqiIh0aZg1nLOcEVI7S4tVxV8xmbjCPCjJO5JufMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="{{ URL::asset('assetsnew/js/app.js') }}"></script>
	<script>
		PNotify.defaults.styling = 'bootstrap4';
	</script>

	@yield('page_script')

	<!-- Dialog -->
	<link href="{{ URL::asset('css/dialog.css') }}" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="{{ URL::asset('js/dialog.js') }}"></script>
	
	
	<script type="text/javascript" src="{{ URL::asset('js/autofill.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/select-custom.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/modal.js') }}"></script>
    <!-- <script type="text/javascript" src="{{ URL::asset('js/app.js') }}?v={{ app_version() }}"></script> -->
	<script type="text/javascript" src="{{ URL::asset('js/mc_modal.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/iframe_modal.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/mc.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/popup.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets2/js/box.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets2/js/list.js') }}"></script>
	<!-- /theme JS files -->
	<script src="{{ URL::asset('assets2/js/app.js') }}"></script>
	
	
	@include('layouts._script_vars')
	@include('layouts._menu_script')

	<script>
		$( document ).ajaxError(function( event, request, settings ) {
			if(typeof(settings.globalError) != 'undefined' || settings.globalError == false) {
				return;
			}

			// abort ajax
			if (request.statusText =='abort') {
				return;
			}

			alert(request.responseText);
		});
	</script>