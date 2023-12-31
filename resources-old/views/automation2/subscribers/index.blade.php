@extends('layouts.automation.frontend')

@section('title', $list->name . ": " . trans('messages.subscribers'))

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
		<li>{{ $automation->name }}</li>
	</ul>

	<h1>
		<span class="text-semibold">{{ trans('messages.subscribers') }}</span>
	</h1>
@endsection

@section('content')

	<form class="listing-form"
		data-url="{{ action('Automation2Controller@subscribersList', $automation->uid) }}"
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
		</div>

		<div class="pml-table-container">



		</div>
	</form>

	<script>

		 $('[name=switch_automation]').change(function() {
                var val = $(this).val();
                window.location = val;
                // var text = $('[name=switch_automation] option:selected').text();
                // var confirm = "{{ trans('messages.automation.switch_automation.confirm') }} <span class='font-weight-semibold'>" + text + "</span>"; 

                // var dialog = new Dialog('confirm', {
                //     message: confirm,
                //     ok: function(dialog) {
                //         window.location = val; 
                //     },
                //     cancel: function() {
                //         $('[name=switch_automation]').val('');
                //     },
                //     close: function() {
                //         $('[name=switch_automation]').val('');
                //     },
                // });
            });

		var bulkDeletePopup = new Popup();

		$('.bulk-delete').click(function(e) {
			e.preventDefault();

			var url = $(this).attr('href');
			
			bulkDeletePopup.load(url);
		});
	</script>
@endsection
