@extends('layouts.frontend')

@section('title', trans('messages.stores_connections'))

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
			<span class="text-semibold"><i class="icon-list2"></i> {{ trans('messages.stores_connections') }}</span>
		</h1>
	</div>
@endsection

@section('content')
    <form class="listing-form"
        data-url="{{ action('SourceController@listing') }}"
        per-page="{{ Acelle\Model\Source::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-9">
                <div class="filter-box">
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
                    <span class="text-nowrap">
                        <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                        <i class="icon-search4 keyword_search_button"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-3 text-right">
                <a href="{{ action("SourceController@create") }}" type="button" class="btn bg-info-800 m-icon">
                    <span class="material-icons-outlined">add</span> {{ trans('messages.source.add_new') }}
                </a>
            </div>
        </div>

        <div class="pml-table-container"></div>
    </form>
@endsection
