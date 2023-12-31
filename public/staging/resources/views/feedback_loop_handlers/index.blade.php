@extends('layouts.frontend')

@section('title', trans('messages.feedback_loop_handlers'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="row">
        <div class="col-md-10">
            <div class="page-title">
                <ul class="breadcrumb breadcrumb-caret position-right">
                    <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
                    <li>Sending</li>
                    <li><a href="javascript:void(0);">{{ trans('messages.feedback_loop_handlers') }}</a></li>
                </ul>
                <h1>
                    <span class="text-semibold"><i class="fe-send"></i> {{ trans('messages.feedback_loop_handlers') }}</span>
                </h1>
                <p>{{ trans('messages.bounce_handler.intro') }}</p>
            </div>
        </div>
    </div>

@endsection

@section('content')

    <form class="listing-form"
        sort-url="{{ action('FeedbackLoopHandlerController@sort') }}"
        data-url="{{ action('FeedbackLoopHandlerController@listing') }}"
        per-page="{{ Acelle\Model\FeedbackLoopHandler::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-9">
                @if ($items->count() >= 0)
                    <div class="filter-box">
                        <div class="btn-group list_actions hide">
                            <button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
                                {{ trans('messages.actions') }} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a delete-confirm="{{ trans('messages.delete_feedback_loop_handlers_confirm') }}" href="{{ action('FeedbackLoopHandlerController@delete') }}"><i class="icon-trash"></i> {{ trans('messages.delete') }}</a></li>
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
                                <option value="feedback_loop_handlers.created_at">{{ trans('messages.created_at') }}</option>
                                <option value="feedback_loop_handlers.name">{{ trans('messages.name') }}</option>
                                <option value="feedback_loop_handlers.updated_at">{{ trans('messages.updated_at') }}</option>
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
                @endif
            </div>
            
                <div class="col-md-3 text-right">
                    <a href="{{ action('FeedbackLoopHandlerController@create') }}" type="button" class="btn bg-info-800">
                        <i class="icon icon-plus2"></i> {{ trans('messages.create_feedback_loop_handler') }}
                    </a>
                </div>
            
        </div>

        <div class="pml-table-container table-responsive">
        </div>
    </form>
@endsection
