@extends('layouts.frontend')

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('content')
	<div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="https://rightreachdemo.devwebsite.co.in">Home</a></li>
            <li class="active">{{ trans('messages.subscribers') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-list2"></i> {{ trans('messages.subscribers') }}</span>
        </h1>
    </div>
	
	

	<form class="listing-form subscribers-list"
		data-url="{{ action('SubscriberController@listSubscribersListing') }}"
		per-page="{{ Acelle\Model\Subscriber::$itemsPerPage }}"
	>
		<div class="row top-list-controls">
			<div class="col-md-10">
				<div class="filter-box">
					<span class="mr-10">
						@include('helpers.select_tool', [
							'disable_all_items' => false
						])
						
					</span>
					<span class="filter-group">
						<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
						<select class="select" name="sort-order">
							<option value="subscribers.email">{{ trans('messages.email') }}</option>
							<option value="subscribers.created_at">{{ trans('messages.created_at') }}</option>
							<option value="subscribers.updated_at">{{ trans('messages.updated_at') }}</option>
						</select>
						<button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
							<i class="icon-sort-amount-asc"></i>
						</button>
					</span>
					<span class="">
						<select class="select" name="status">
							<option value="">{{ trans('messages.all_subscribers') }}</option>
							<option value="subscribed">{{ trans('messages.subscribed') }}</option>
							<option value="unsubscribed">{{ trans('messages.unsubscribed') }}</option>
							<option value="unconfirmed">{{ trans('messages.unconfirmed') }}</option>
							<option value="spam-reported">{{ trans('messages.spam-reported') }}</option>
							<option value="blacklisted">{{ trans('messages.blacklisted') }}</option>
						</select>
					</span>
					<span class="filter-group ml-10">
						<select class="select" name="verification_result">
							<option value="">{{ trans('messages.all_verification') }}</option>
							@foreach (Acelle\Model\EmailVerification::resultSelectOptions() as $option)
								<option value="{{ $option['value'] }}">
									{{ $option['text'] }}
								</option>
							@endforeach
						</select>
					</span>
					<div class="btn-group list_columns mr-10">
						<button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
							{{ trans('messages.columns') }} <span class="caret"></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-right">
								
								
							<li>
								<div class="checkbox">
									<label>
										<input checked="checked" type="checkbox" id="created_at" name="columns_[]" value="created_at" class="styled">
										{{ trans('messages.created_at') }}
									</label>
								</div>
							</li>
							<li>
								<div class="checkbox">
									<label>
										<input checked="checked" type="checkbox" id="updated_at" name="columns_[]" value="updated_at" class="styled">
										{{ trans('messages.updated_at') }}
									</label>
								</div>
							</li>
						</ul>
					</div>
					<span class="text-nowrap">
						<input name="keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
						<i class="icon-search4 keyword_search_button"></i>
					</span>
				</div>
			</div>
			<!-- <div class="col-md-2 text-right">
				<a href="{{ action("SubscriberController@createSubscriber") }}" type="button" class="btn bg-info-800">
					<i class="icon icon-plus2"></i> {{ trans('messages.create_subscriber') }}
				</a>
			</div> -->
		</div>

		<div class="pml-table-container">



		</div>
	</form>

	<script>
		var bulkDeletePopup = new Popup();

		$('.bulk-delete').click(function(e) {
			e.preventDefault();

			var url = $(this).attr('href');
			
			bulkDeletePopup.load(url);
		});
		
		var assignValues = new Popup();
		$('.assign-values-button').click(function(e) {
			e.preventDefault();

        	var vals = $(".subscribers-list input[name='ids[]']:checked").map(function () {
				return this.value;
			}).get();

			var url = $(this).attr('href');
			
			assignValues.load(url, null, {uids: vals});
		});
	</script>
@endsection
