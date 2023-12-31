@extends('layouts.backend')

@section('title', trans('messages.create_plan'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PlanController@index") }}">{{ trans('messages.plans') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.create_plan') }}</span>
        </h1>
    </div>

@endsection

@section('content')
	<div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
			<p>{{ trans('messages.plan_create_message') }}</p>
			<form enctype="multipart/form-data" action="{{ action('Admin\PlanController@store') }}" method="POST" class="form-validate-jqueryz">
				{{ csrf_field() }}
				@include('admin.plans._form')
			<form>
		</div>
	</div>
@endsection
