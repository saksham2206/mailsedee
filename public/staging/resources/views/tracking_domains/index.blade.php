@extends('layouts.frontend')

@section('title', trans('messages.tracking_domains'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("TrackingDomainController@index") }}">{{ trans('messages.tracking_domains') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.tracking_domains') }}</span>
        </h1>           
    </div>

@endsection

@section('content')
    
	<div class="row">
        <div class="col-sm-12 col-md-10 col-lg-10">
            <p>{!! trans('messages.tracking_domain.wording') !!}</p>
        </div>
    </div>

    <form class="listing-form"
        data-url="{{ action('TrackingDomainController@listing') }}"
        per-page="{{ Acelle\Model\TrackingDomain::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-9">
                @if ($trackingDomains->count() >= 0)
                    <div class="filter-box">
                        <div class="btn-group list_actions hide">
                            <button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
                                {{ trans('messages.actions') }} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a delete-confirm="{{ trans('messages.delete_tracking_domains_confirm') }}" href="{{ action('TrackingDomainController@delete') }}"><i class="icon-trash"></i> {{ trans('messages.delete') }}</a></li>
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
                                <option value="tracking_domains.name">{{ trans('messages.name') }}</option>
                                <option value="tracking_domains.created_at">{{ trans('messages.created_at') }}</option>
                                <option value="tracking_domains.updated_at">{{ trans('messages.updated_at') }}</option>
                            </select>
                            <button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                                <i class="icon-sort-amount-asc"></i>
                            </button>
                        </span>
                        <span class="text-nowrap">
                            <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                            <i class="icon-search4 keyword_search_button"></i>
                        </span>
                    </div>
                @endif
            </div>
            @if (Auth::user()->customer->can('create', new Acelle\Model\TrackingDomain()))
                <div class="col-md-3 text-right">
                    <a href="{{ action('TrackingDomainController@create') }}" type="button" class="btn bg-info-800">
                        <i class="icon icon-plus2"></i> {{ trans('messages.tracking_domain.create') }}
                    </a>
                </div>
            @endif
        </div>

        <div class="pml-table-container"></div>
    </form>
@endsection
