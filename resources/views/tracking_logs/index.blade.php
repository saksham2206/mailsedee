@extends('layouts.frontend')

@section('title', trans('messages.tracking_log'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>		
@endsection

@section('page_header')

			<div class="page-title">				
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
					<li>Reports</li>
					<li><a href="javascript:void(0);">{{ trans('messages.tracking_log') }}</a></li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="fa fa-file-text-o"></i> {{ trans('messages.tracking_log') }}</span>
				</h1>				
			</div>

@endsection

@section('content')
				
				<!-- <form class="listing-form"
					data-url="{{ action('TrackingLogController@listing') }}"
					per-page="{{ Acelle\Model\TrackingLog::$itemsPerPage }}"					
				>	 -->			
					<!-- <div class="row top-list-controls">
						<div class="col-md-9">
							@if ($items->count() >= 0)					
								<div class="filter-box">
									<span class="filter-group">
										<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
										<select class="select" name="sort-order">
                                            <option value="tracking_logs.created_at">{{ trans('messages.created_at') }}</option>
										</select>										
										<button class="btn btn-xs sort-direction" rel="desc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
											<i class="icon-sort-amount-desc"></i>
										</button>
									</span>
									<span class="ml-10">										
										<select data-placeholder="{{ trans('messages.all_campaigns') }}" class="select2-ajax" name="campaign_uid" data-url="{{ action('CampaignController@select2') }}">
											
										</select>								
									</span>
									<span class="text-nowrap">
										<input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
										<i class="icon-search4 keyword_search_button"></i>
									</span>
								</div>
							@endif
						</div>
					</div> -->
					
					<div class="pml-table-container table-responsive">
						
						@if ($items->count() > 0)
							<table class="table pml-table1 table-log"
						    >
						    	<thead>
						    		<tr>
										<th>{{ trans('messages.recipient') }}</th>
										<th>{{ trans('messages.status') }}</th>
										<th>{{ trans('messages.campaign') }}</th>
										<th>{{ trans('messages.sending_server') }}</th>
										<th>{{ trans('messages.created_at') }}</th>
									</tr>
						    	</thead>
								<tbody>
								@foreach ($items as $key => $item)
								
									<tr>
										<td>
											<span class="no-margin kq_search">{{ $item->subscriber->email }}</span>
											<!-- <span class="text-muted second-line-mobile">{{ trans('messages.recipient') }}</span> -->
										</td>
										<td>
											<span class="no-margin">
												<span data-popup="tooltip" title="{{ $item->error }}" class="label label-flat bg-{{ $item->status }} kq_search">{{ trans('messages.tracking_log_status_' . $item->status) }}</span>
											</span>
											<!-- <span class="text-muted second-line-mobile">{{ trans('messages.status') }}</span> -->
										</td>
										<td>
											<span class="no-margin kq_search">{{ is_null($item->campaign) ? 'N/A' : $item->campaign->name }}</span>
											<!-- <span class="text-muted second-line-mobile">{{ trans('messages.campaign') }}</span> -->
										</td>
										<td>
											<span class="no-margin kq_search">{{ $item->sendingServer->name }}</span>
											<!-- <span class="text-muted second-line-mobile">{{ trans('messages.sending_server') }}</span> -->
										</td>
										<td>
											<span class="no-margin kq_search">{{ Tool::formatDateTime($item->created_at) }}</span>
											<!-- <span class="text-muted second-line-mobile">{{ trans('messages.created_at') }}</span> -->
										</td>
									</tr>

								@endforeach
								</tbody>
							</table>

						@endif
						
					</div>
				<!-- </form>	 -->		
				<script type="text/javascript">
	$(document).ready(function() {
	    $('.pml-table1').DataTable({
	    	
	    	dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
	    });
	} );
</script>

@endsection


