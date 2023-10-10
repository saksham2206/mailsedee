@extends('layouts.popup.large')

@section('content')
	<div class="row">
        <div class="col-md-12">
            <h2>{{ trans('messages.campaign.choose_your_template_layout') }}</h2>
			@include('campaigns.template._tabs')
			<div class="tab-content" id="pills-tabContent">
				<div class="">
					<div class="subsection pb-4">
						<h2 class="font-weight-semibold mb-0">{{ $category->name }}</h2>
						<hr>

						<div id="gallery" class="pb-4">
							<form class="listing-form"
								data-url="{{ action('CampaignController@templateLayoutList', [
									'uid' => $campaign->uid,
									'category_uid' => $category->uid,
								]) }}"
								per-page="25"					
							>				
								<div class="row top-list-controls">
									<div class="col-md-9">
										<div class="filter-box">
											<span class="d-flex align-items-center mr-4">
												<span class="title text-semibold text-muted mr-2">{{ trans('messages.sort_by') }}</span>
												<select class="select mr-3" name="sort-order">
													<option value="id">{{ trans('messages.default') }}</option>
													<option value="created_at">{{ trans('messages.created_at') }}</option>
													<option value="name">{{ trans('messages.name') }}</option>
												</select>										
												<button class="btn btn-xs sort-direction mr-3 ml-2" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
													<i class="icon-sort-amount-asc"></i>
												</button>
												<span class="title text-semibold text-muted mr-2">{{ trans('messages.from') }}</span>
												<select class="select" name="from">
													<option value="all" selected='selected'>{{ trans('messages.all') }}</option>
													<option value="mine">{{ trans('messages.my_templates') }}</option>
													<option value="gallery">{{ trans('messages.gallery') }}</option>
												</select>
												<input name="search_keyword ml-3" style="margin-left: 15px" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
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
				</div>
			</div>
        </div>
    </div>
        
    <script>
        $('a.choose-template-tab').click(function(e) {
            e.preventDefault();
        
            var url = $(this).attr('href');
        
            templatePopup.load(url);
        });
        
        var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            window.location = '{{ action('CampaignController@template', $campaign->uid) }}';
        }});
    </script>
@endsection