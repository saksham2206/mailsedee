@if ($sources->count() > 0)
	<table class="table table-box pml-table"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($sources as $key => $source)
			<tr>
				<td width="1%">
					<div class="product-image-list mr-3">
						<img src="{{ url('images/' . $source->type . '_list.png') }}" />
					</div>
				</td>
				<td>
					<a href="{{ action('SourceController@show', $source->uid) }}">
						<h5 class="no-margin text-normal m-0"><span class="kq_search" href="javascript:;">
							{{ $source->getName() }}
						</span></h5>
					</a>
					<span class="text-muted d-block mt-2">
						{{ trans('messages.created_at') }}:
						{{ Tool::formatDateTime($source->created_at) }}
					</span>
				</td>				
				<td>
					<h5 class="no-margin">
						{{ format_number($source->productsCount()) }}
					</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.products') }}</span>
				</td>
				<td>
					<h5 class="no-margin">
						{{ Tool::formatDateTime($source->updated_at) }}
					</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.source.last_sync_at') }}</span>
				</td>
				<td class="text-right">
					<a href="{{ action('SourceController@sync', $source->uid) }}"
						link-method="POST"
						type="button" class="btn bg-teal-600 m-icon">
						<span class="material-icons">
							sync
							</span>{{ trans('messages.source.sync') }}</a>
					<div class="btn-group">
						<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li>
								<a delete-confirm="{{ trans('messages.source.delete.confirm') }}"
									data-method="POST"
									href="{{ action('SourceController@delete', ['uids' => $source->uid]) }}">
									<i class="icon-trash"></i> {{ trans('messages.delete') }}
								</a>
							</li>
						</ul>
					</div>
				</td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $sources])
		
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<span class="material-icons-outlined">
			legend_toggle
			</span>
		<span class="line-1">
			{{ trans('messages.source.not_found') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<span class="material-icons-outlined">
			legend_toggle
			</span>
		<span class="line-1">
			{{ trans('messages.source.empty') }}
		</span>
		<span class="line-2">
			{{ trans('messages.list_empty_line_2') }}
		</span>
	</div>
@endif
