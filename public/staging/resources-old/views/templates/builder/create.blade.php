@extends('layouts.frontend')

@section('title', trans('messages.create_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
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
    
    <div class="row">
        <div class="col-md-6">
            <form action="{{ action('TemplateController@builderCreate') }}" method="POST" class="template-form form-validate-jquery">
                {{ csrf_field() }}
                
				<input type="hidden" value="" name="template" />
                
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
            </form>
        </div>
    </div>
        
    <div class="row">
        <div class="col-md-12" style="position: relative;">
			<div class="d-flex align-items-center mt-4 template-create-sticky">
				<h3 class="text-semibold mr-auto mb-0 mt-0">{{ trans('messages.template.select_your_template') }}</h3>
				<div class="text-left">
					<button class="btn bg-teal mr-10 start-design"><i class="icon-check"></i> {{ trans('messages.template.create_and_design') }}</button>
				</div>
			</div>

			@foreach (Acelle\Model\TemplateCategory::all() as $category)
				@if ($category->templates()->count())
					<div class="subsection pb-4">
						<h2 class="font-weight-semibold mb-0">{{ $category->name }}</h2>
						<hr>

						<div id="gallery" class="pb-4">
							<form class="listing-form"
								data-url="{{ action('TemplateController@builderTemplates', [
									'category_uid' => $category->uid,
								]) }}"
								per-page="25"					
							>				
								<div class="row top-list-controls">
									<div class="col-md-9">
										<div class="filter-box">
											<span class="filter-group">
												<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
												<select class="select" name="sort-order">
													<option value="id">{{ trans('messages.default') }}</option>
													<option value="created_at">{{ trans('messages.created_at') }}</option>
													<option value="name">{{ trans('messages.name') }}</option>
												</select>										
												<button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
													<i class="icon-sort-amount-asc"></i>
												</button>
											</span>
											<span class="text-nowrap">
												<input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
												<i class="icon-search4 keyword_search_button"></i>
											</span>
										</div>
									</div>
								</div>
								
								<div class="pml-table-container">
								</div>
							</form>
						</div>
						<br style="clear:both" /><br style="clear:both" />
					</div>
				@endif
			@endforeach
        </div>
    </div>
    
        
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
            
            $('.select-template-layout').eq(0).click();
            
            $(document).on('click', '.start-design', function() {
                var form = $('.template-form');
				
				if ($('.select-template-layout.selected').length == 0) {
					// Success alert
					swal({
						title: "{{ trans('messages.template.need_select_template') }}",
						text: "",
						confirmButtonColor: "#666",
						type: "error",
						allowOutsideClick: true,
						confirmButtonText: "{{ trans('messages.ok') }}",
						customClass: "swl-error",
						html:true
					});
					return;
				}
                
                if (form.valid()) {
                    form.submit();
                }
            });
        });
    </script>
    
@endsection
