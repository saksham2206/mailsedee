@extends('layouts.frontend')

@section('title', trans('messages.subscribers'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

	<ul class="breadcrumb breadcrumb-caret position-right">
		<li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
        <li><a href="{{ action("Automation2Controller@index") }}">{{ trans('messages.automations') }}</a></li>
        
	</ul>

	<h1>
		<span class="text-semibold">{{ $subscriber->email }}</span>
	</h1>
@endsection

@section('content')
	ssss
@endsection
