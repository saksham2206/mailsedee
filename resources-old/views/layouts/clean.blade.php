<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')


	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="{{ URL::asset('assetsnew/css/config/modern/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ URL::asset('assetsnew/css/config/modern/app.min.css') }}" rel="stylesheet" type="text/css">
	
	<link href="{{ URL::asset('assetsnew/css/icons.min.css') }}" rel="stylesheet" type="text/css">
	<!-- <link href="{{ URL::asset('css/app.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css"> -->
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/vendor.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/app.min.js') }}"></script>
	<!-- <script type="text/javascript" src="{{ URL::asset('assetsnew/js/core/libraries/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/loaders/blockui.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/ui/nicescroll.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/ui/drilldown.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/notifications/sweet_alert.min.js') }}"></script> -->
	<!-- /core JS files -->

	<!-- Theme JS files -->
<!-- 	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/plugins/forms/styling/uniform.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('assetsnew/js/core/app.js') }}"></script> -->
	<!-- /theme JS files -->

	<!-- <script>
		$(function() {

			// Style checkboxes and radios
			$('.styled').uniform();

		});
	</script> -->
	<link href="{{ URL::asset('assets/css/components.css') }}" rel="stylesheet" type="text/css">
		<link href="{{ URL::asset('css/mc.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css">
		<link href="{{ URL::asset('assetsnew/css/newAuth.css') }}?v={{ app_version() }}" rel="stylesheet" type="text/css">
		


	<!-- display flash message -->
	@include('common.flash')

</head>

<body class="loading auth-fluid-pages pb-0">
	 <div class="auth-fluid">
        @yield('content')
    </div>


@include('layouts._js')
</body>
</html>
