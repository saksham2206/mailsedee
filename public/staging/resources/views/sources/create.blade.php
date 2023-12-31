@extends('layouts.frontend')

@section('title', trans('messages.stores_connections'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SourceController@index") }}">{{ trans('messages.stores_connections') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.source.add_new') }}</span>
		</h1>
	</div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <p>{{ trans('messages.source.select_source_type.wording') }}</p>
            <div class="source_list d-flex align-items-center">
                @if (Acelle\Model\Plugin::enabled('acelle/lazada'))
                    <a class="source_type mr-4" href="{{ $lazadaConnectLink }}">
                        <img width="200px" src="{{ url('images/Lazada.svg') }}" />
                    </a>
                @endif
                
                <a class="source_type woocommerce-create"
                    href="{{ action('SourceController@wooConnect') }}"
                >
                    <img width="200px" src="{{ url('images/woocommerce.svg') }}" />
                </a>
            </div>
        </div>
    </div>
    <script>
        var wooPopup = new Popup();

        $('.woocommerce-create').on('click', function(e) {
            e.preventDefault();

            var url = $(this).attr('href');

            wooPopup.load(url);
        });

    </script>
@endsection
