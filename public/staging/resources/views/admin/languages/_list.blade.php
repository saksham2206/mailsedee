@if ($languages->count() > 0)
	<table class="table table-box pml-table"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($languages as $key => $language)
			<tr>
				<td>
					<h5 class="no-margin text-bold">
						@can("delete", $language)
							<a class="kq_search" href="{{ action('Admin\LanguageController@edit', $language->uid) }}">{{ $language->name }}</a>
						@else
							{{ $language->name }}
						@endcan
					</h5>
					<span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($language->created_at) }}</span>
				</td>
				<td>
					<span class="no-margin stat-num kq_search">{{ $language->code }}</span>
					<br />
					<span class="text-muted">{{ trans('messages.code') }}</span>
				</td>
				<td class="text-center">
					<span class="text-muted2 list-status">
						<span class="label label-flat bg-{{ $language->status }}">{{ trans('messages.language_status_' . $language->status) }}</span>
					</span>	
				</td>
				<td class="text-right">																					
					@can("translate", $language)
						<a href="{{ action('Admin\LanguageController@translate', ["id" => $language->uid, "file" => "messages", "file_id" => $language->getDefaultFile()['id']]) }}" data-popup="tooltip" title="{{ trans('messages.translate') }}" type="button" class="btn bg-teal btn-icon"><i class="icon-share2"></i> {{ trans('messages.translate') }}</a>
					@endcan
					@if(Auth::user()->can("delete", $language) ||
						Auth::user()->can("update", $language) ||
						Auth::user()->can("enable", $language) ||
						Auth::user()->can("disable", $language) ||
						Auth::user()->can("upload", $language) ||
						Auth::user()->can("download", $language)
					)
						<div class="btn-group">										
							<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">
								@can('enable', $language)
									<li>														
										<a link-confirm="{{ trans('messages.enable_languages_confirm') }}" href="{{ action('Admin\LanguageController@enable', ["uids" => $language->uid]) }}">
											<i class="icon-checkbox-checked2"></i> {{ trans('messages.enable') }}
										</a>
									</li>
								@endcan
								@can('disable', $language)
									<li>														
										<a link-confirm="{{ trans('messages.disable_languages_confirm') }}" href="{{ action('Admin\LanguageController@disable', ["uids" => $language->uid]) }}">
											<i class="icon-checkbox-unchecked2"></i> {{ trans('messages.disable') }}
										</a>
									</li>
								@endcan
								@can("download", $language)
									<li>
										<a href="{{ action('Admin\LanguageController@download', $language->uid) }}" data-popup="tooltip" title="{{ trans('messages.download') }}"><i class="icon-download"></i> {{ trans('messages.download') }}</a>
									</li>
								@endcan
								@can("upload", $language)
									<li>
										<a href="{{ action('Admin\LanguageController@upload', $language->uid) }}" data-popup="tooltip" title="{{ trans('messages.upload') }}"><i class="icon-upload"></i> {{ trans('messages.upload') }}</a>
									</li>
								@endcan
								@can("update", $language)
									<li>
										<a href="{{ action('Admin\LanguageController@edit', $language->uid) }}" data-popup="tooltip" title="{{ trans('messages.edit') }}"><i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
									</li>
								@endcan
								@can("delete", $language)
									<li>
										<a list-delete-confirm="{{ action('Admin\LanguageController@deleteConfirm', ['uids' => $language->uid]) }}" href="{{ action('Admin\LanguageController@delete', ["uids" => $language->uid]) }}">
											<i class="icon-trash"></i> {{ trans('messages.delete') }}
										</a>
									</li>
								@endcan
								</li>
							</ul>
						</div>
					@endcan
				</td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', [
		'items' => $languages,
	])
	
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<span class="material-icons-outlined">flag</span>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else					
	<div class="empty-list">
		<span class="material-icons-outlined">flag</span>
		<span class="line-1">
			{{ trans('messages.language_empty_line_1') }}
		</span>
	</div>
@endif