@extends('layouts.backend')

@section('title', trans('messages.create_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
	<script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
@endsection

@section('page_header')

    <div class="page-title">				
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("TemplateController@index") }}">{{ trans('messages.templates') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.template.new_template') }}</span>
        </h1>				
    </div>

@endsection

@section('content')
    
    <form action="{{ action('TemplateController@updateTemplate',$template->uid) }}" method="POST" class="template-form form-validate-jquery">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-6">

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
					<button type="submit" class="btn bg-teal mr-10 start-design"><i class="icon-check"></i> Update Template</button>
				</div>
			</div>

        </div>
    </div>
    </form>
        
    <script>	
        $(document).ready(function() {
            $(document).on('click', '.select-template-layout', function() {
				var template = $(this).attr('data-template');
                
                // unselect all layouts
                $('.select-template-layout').removeClass('selected');
                
                // select this
                $(this).addClass('selected');

				// unselect all
				$('[name=template]').val('');
				
				// update template value
				if (typeof(template) !== 'undefined') {
					$('[name=template]').val(template);
				}
            });

            CKEDITOR.replace( 'content' )

            });
            
    </script>
    
@endsection
