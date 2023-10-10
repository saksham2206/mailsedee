@if ($products->count() > 0)
	<table class="table table-box pml-table"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($products as $key => $product)
			<tr>
				<td width="1%">
					<div class="product-image-list mr-3">
						<img src="{{ action('ProductController@image', $product->uid) }}" />
					</div>
				</td>
				<td width="50%">
					<h5 class="no-margin text-normal">
						<span class="kq_search" href="javascript:;">
							{{ $product->title }}
						</span>
					</h5>
					<span class="text-muted d-block mt-2">
						{{ trans('messages.created_at') }}:
						{{ Tool::formatDateTime($product->created_at) }}
					</span>
				</td>
				<td>
					<h5 class="no-margin">
						{{ $product->source->getName() }}
					</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.source') }}</span>
				</td>
				<td class="text-right">
					<a href="{{ action('SourceController@sync', $product->uid) }}"
						link-method="POST"
						type="button" class="btn btn-mc_primary m-icon pl-3">
						<span class="material-icons-outlined mr-2">
							link
							</span>{{ trans('messages.view') }}</a>
					<div class="btn-group">
						<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<li>
								<a delete-confirm="{{ trans('messages.source.delete.confirm') }}"
									data-method="POST"
									href="{{ action('SourceController@delete', ['uids' => $product->uid]) }}">
									<i class="icon-trash"></i> {{ trans('messages.delete') }}
								</a>
							</li>
						</ul>
					</div>
				</td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $products])
		
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<span class="material-icons-outlined">
			source
			</span>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<span class="material-icons-outlined">
			source
			</span>
		<span class="line-1">
			{{ trans('messages.list_empty_line_1') }}
		</span>
		<span class="line-2">
			{{ trans('messages.list_empty_line_2') }}
		</span>
	</div>
@endif
