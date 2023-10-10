@extends('layouts.backend')

@section('title', trans('messages.plans'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>

    <script>
        $(document).on('click', '.mc-modal-back', function(e) {
            e.preventDefault();

            mcModal.goToUrl($(this).attr('href'));
        });

        var initMcModal = function() {
            var mcModal = {};
            mcModal.modal = $('#mcModal');
            mcModal.show = function() {
                var load = htmlLoader();
                var html = '<div class="mc-modal modal fade" role="dialog" id="mcModal">'+
                    '<div class="modal-dialog modal-lg modal-select">'+
                        '<div class="modal-content">'+
                            '<div class="p-10">'+load+'</div>'+
                        '</div>'+
                    '</div>'+
                '</div>';

                mcModal.modal.remove();
                $('body').append(html);
                mcModal.modal = $('#mcModal');

                mcModal.modal.modal('show');
            };

            mcModal.goToUrl = function(url, method) {
                if (typeof(method) === 'undefined') {
                    method = 'GET';
                }

                // show modal
                mcModal.show();

                // ajax load url
                $.ajax({
                    url: url,
                    method: method,
                }).always(function(response) {
                    mcModal.fill(response);
                });
            }

            mcModal.fill = function(html) {
                mcModal.modal.find('.modal-content').html(html);
                applyJs(mcModal.modal);
            }

            return mcModal;
        };

        var mcModal = initMcModal();

        $(document).ready(function() {
            $('.modal-action').click(function(e) {
                e.preventDefault();

                mcModal.goToUrl($(this).attr('href'));
            });
        });
    </script>
@endsection

@section('page_header')

	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-list2"></i> {{ trans('messages.plans') }}</span>
		</h1>
	</div>

@endsection

@section('content')

	<p>{{ trans('messages.plan_create_message') }}</p>

    <form class="listing-form"
        sort-url="{{ action('Admin\PlanController@sort') }}"
        data-url="{{ action('Admin\PlanController@listing') }}"
        per-page="{{ Acelle\Model\Plan::$itemsPerPage }}"
    >
        <div class="row top-list-controls">
            <div class="col-md-10">
                @if ($plans->count() >= 0)
                    <div class="filter-box">
                        <span class="filter-group">
                            <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                            <select class="select" name="sort-order">
                                <option value="plans.created_at">{{ trans('messages.created_at') }}</option>
                                <option value="plans.name">{{ trans('messages.name') }}</option>
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
            @can('create', new Acelle\Model\Plan())
                <div class="col-md-2 text-right">
                    <a href="{{ action("Admin\PlanController@wizard") }}" type="button" class="btn bg-info-800 modal-action">
                        <i class="icon icon-plus2"></i> {{ trans('messages.create_plan') }}
                    </a>
                </div>
            @endcan
        </div>

        <div class="pml-table-container">
        </div>
    </form>

    <script type="text/javascript">
        // Link confirm
    $(document).on("click", "a[link-confirm]", function(e) {
        
        var mgs = $(this).attr("link-confirm");
        var url = $(this).attr("href");
        var method = $(this).attr("data-method");

        if (typeof(method) === 'undefined' || method.trim() === '') {
            method = 'GET';
        }

        if (typeof($(this).attr('new-href')) === 'undefined' || $(this).attr('new-href') === '') {
            $(this).attr("new-href", $(this).attr("href"));
        }

        if($(this).parents(".list_actions").length) {
            var form = $(this).parents(".listing-form");
            var vals = form.find("input[name='ids[]']:checked").map(function () {
                return this.value;
            }).get();

            var sign = (url.indexOf('?') !== -1 ? '&' : '?');
            url = url + sign + "uids=" + vals.join(",");

            // Select tool
            if (form.find('.select_tool').length && form.find('.select_tool').val() == 'all_items') {
                var select_tool = form.find('.select_tool').val();
                url = url + "&select_tool=" + select_tool;
                url = url + "&" + form.serialize();
            }
        }

        // count items
        var count = 1;
        if (typeof($(this).attr("items-count")) != 'undefined') {
            count = $(this).attr("items-count");
        }

        $('#link_confirm_model').modal("show");

        mgs = mgs.replace(":number", "<span class='text-bold text-teal-800'>" + count + "</span>");
        mgs = mgs.replace(":name", "<span class='text-bold text-teal-800'>" + $(this).html() + "</span>");

        $("#link_confirm_model h6").html(mgs);
        $(".link-confirm-button").attr("href", url);
        $(".link-confirm-button").attr("data-method", method);

        // set method
        if(typeof(method) != 'undefined') {
            $(".link-confirm-button").attr("data-method", method);
        } else {
            $(".link-confirm-button").removeAttr("data-method");
        }

        // Link confirm
        if (typeof($(this).attr('link-method')) !== 'undefined') {
            $(".link-confirm-button").attr("link-method", $(this).attr('link-method'));
            $(".link-confirm-button").removeClass("ajax_link");
        }
        
        // direct link
        if ($(this).hasClass('direct-link')) {
            $(".link-confirm-button").removeClass("ajax_link");
        }

        e.stopImmediatePropagation();
        e.preventDefault();
    });
    </script>
@endsection
