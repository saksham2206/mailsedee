@if ($products->count() > 0)
	<div class="row mt-4">
		@foreach ($products as $key => $product)
			<div class="col-md-3 col-sm-6 mb-4">
				<div class="card mb-4 box-shadow">
					<span class="product-image-box">
						<img class="card-img-top" src="{{ action('ProductController@image', $product->uid) }}" style="height: 100%; width: auto; display: block;">
					</span>
					<div class="card-body p-3">
						<h5 title="{{ $product->title }}" class="text-semibold mt-1 mb-2 text-ellipsis">{{ $product->title }}</h5>
						<p style="display: block;
						height: 50px;
						overflow: hidden;" class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
						<div class="">
							<div class="d-flex align-items-center">
								<button type="button" class="btn btn-mc_primary">{{ trans('messages.view') }}</button>
								<a delete-confirm="{{ trans('messages.source.delete.confirm') }}"
									data-method="POST" href="{{ action('SourceController@delete', ['uids' => $product->uid]) }}"
									class="btn btn-link">
									{{ trans('messages.delete') }}
								</a>
								<span class="text-muted ml-auto text-teal-800 m-icon d-flex align-items-center">
									<img width="20px" class="mr-2 list-source-img" src="{{ url('images/' . $product->source->type . '_list.png') }}" />
									<span>{{ $product->source->getName() }}</span>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	
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
