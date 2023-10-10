@extends('layouts.frontend')

@section('title', trans('messages.products'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-list2"></i> {{ trans('messages.products') }}</span>
		</h1>
	</div>
@endsection

@section('content')
    <form class="listing-form view-{{ request()->view ? request()->view : 'grid' }}"
        data-url="{{ action('ProductController@listing', ['view' => request()->view]) }}"
        per-page="{{ Acelle\Model\Product::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-8">
                <div class="filter-box">
                    <div class="btn-group list_actions hide">
                        <button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
                            {{ trans('messages.actions') }} <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a list-delete-confirm="{{ action('MailListController@deleteConfirm') }}" href="{{ action('MailListController@delete') }}"><i class="icon-trash"></i> {{ trans('messages.delete') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="checkbox inline check_all_list">
                        <label>
                            <input type="checkbox" class="styled check_all">
                        </label>
                    </div>
                    <span class="filter-group">
                        <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                        <select class="select" name="sort-order">
                            <option value="created_at">{{ trans('messages.created_at') }}</option>
                            <option value="name">{{ trans('messages.name') }}</option>
                        </select>
                        <button class="btn btn-xs sort-direction" rel="desc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                            <i class="icon-sort-amount-desc"></i>
                        </button>
                    </span>
                    <span class="filter-group">
                        <span class="title text-semibold text-muted">{{ trans('messages.source') }}</span>
                        <select class="select" name="source_uid">
                            <option value="" class="active">{{ trans('messages.all_source') }}</option>
                            @foreach (Acelle\Model\Source::all() as $source)
                                <option {!! request()->source_uid == $source->uid ? 'selected' : '' !!} value="{{ $source->uid }}" class="active">{{ $source->getName() }}</option>
                            @endforeach
                        </select>
                    </span>
                    <span class="text-nowrap">
                        <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                        <i class="icon-search4 keyword_search_button"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-right d-flex align-items-center">
                <div class="view-toggle d-flex ml-auto">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <a href="{{ action('ProductController@index') }}" class="btn btn-default view-toogle grid m-icon mr-1">
                            <span class="material-icons">
                                grid_view
                            </span>
                        </a>
                        <a href="{{ action('ProductController@index', ['view' => 'list']) }}" class="btn btn-default view-toogle list m-icon mr-3">
                            <span class="material-icons-outlined">
                                reorder
                            </span>
                        </a>
                    </div>
                </div>
                <a href="{{ action("SourceController@index") }}" type="button" class="btn bg-info-800 m-icon">
                    <span class="material-icons-outlined">store</span> {{ trans('messages.stores_connections') }}
                </a>
            </div>
        </div>

        <div class="pml-table-container"></div>
    </form>
@endsection
