@extends('layouts.frontend')

@section('title', 'Unsubscribe List')

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection


@section('content')
	<div class="page-title">
	<h2 class="text-bold text-teal-800 mb-10"><i class="icon-users4"></i> Unsubscriber</h2>
	</div>
	<form class="listing-form subscribers-list"
		data-url="{{ action('SubscriberController@listing', $list->uid) }}"
		per-page="{{ Acelle\Model\Subscriber::$itemsPerPage }}"
	>
		<div class="row top-list-controls">
			<div class="col-md-10">
				<div class="filter-box">
					<span class="mr-10">
						@include('helpers.select_tool', [
							'disable_all_items' => false
						])
						<div class="btn-group list_actions hide mr-0">
							<button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
								{{ trans('messages.actions') }} <span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<li>
									<a class="assign-values-button" href="{{ action('SubscriberController@assignValues', $list->uid) }}">
										<i class="icon-add"></i> {{ trans('messages.subscriber.assign_values') }}
									</a>
								</li>
								<li>
									<a link-confirm="{{ trans('messages.subscribe_subscribers_confirm') }}" href="{{ action('SubscriberController@subscribe', $list->uid) }}">
										<i class="icon-enter"></i> {{ trans('messages.subscribe') }}
									</a>
								</li>
								<li>
									<a link-confirm="{{ trans('messages.unsubscribe_subscribers_confirm') }}" href="{{ action('SubscriberController@unsubscribe', $list->uid) }}">
										<i class="icon-exit"></i> {{ trans('messages.unsubscribe') }}
									</a>
								</li>
								<li>
									<a data-method="POST" link-confirm="{{ trans('messages.subscribers.resend_confirmation_email.confirm') }}" href="{{ action('SubscriberController@resendConfirmationEmail', $list->uid) }}">
										<i class="icon-envelop5"></i> {{ trans('messages.subscribers.resend_confirmation_email') }}
									</a>
								</li>
								<li>
									<a href="#" class="copy_move_subscriber"
										data-url="{{ action('SubscriberController@copyMoveForm', [
											'from_uid' => $list->uid,
											'action' => 'copy',
										]) }}">
											<i class="icon-copy4"></i> {{ trans('messages.copy_to') }}
									</a>
								</li>
								<li>
									<a href="#move" class="copy_move_subscriber"
										data-url="{{ action('SubscriberController@copyMoveForm', [
											'from_uid' => $list->uid,
											'action' => 'move',
										]) }}">
										<i class="icon-move-right"></i> {{ trans('messages.move_to') }}
									</a>
								</li>
								<li>
									<a delete-confirm="{{ trans('messages.delete_subscribers_confirm') }}" href="{{ action('SubscriberController@delete', $list->uid) }}">
										<i class="icon-trash"></i> {{ trans('messages.delete') }}
									</a>
								</li>
								<li>
									<a href="{{ action('SubscriberController@bulkDelete', $list->uid) }}" class="bulk-delete">
										<i class="icon-trash"></i> {{ trans('messages.subscriber.bulk_delete') }}
									</a>
								</li>
							</ul>
						</div>
						<!--<div class="checkbox inline check_all_list">
							<label>
								<input type="checkbox" class="styled check_all">
							</label>
						</div>-->
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

							@foreach ($list->getFields as $field)
								@if ($field->tag != "EMAIL")
									<li>
										<div class="checkbox">
											<label>
												<input {{ (true ? "checked='checked'" : "") }} type="checkbox" id="{{ $field->tag }}" name="columns_[]" value="{{ $field->uid }}" class="styled">
												{{ $field->label }}
											</label>
										</div>
									</li>
								@endif
							@endforeach
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
			<div class="col-md-2 text-right">
				<a href="{{ action("SubscriberController@create", $list->uid) }}" type="button" class="btn bg-info-800">
					<i class="icon icon-plus2"></i> {{ trans('messages.create_subscriber') }}
				</a>
			</div>
		</div>

		<div class="pml-table-container table-responsive">



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
