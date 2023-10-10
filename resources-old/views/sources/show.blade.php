@extends('layouts.frontend')

@section('title', trans('messages.stores_connections'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SourceController@index") }}">{{ trans('messages.stores_connections') }}</a></li>
		</ul>
	</div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="source-header d-flex">
                <div class="source-logo mr-30">
                    <img src="{{ url('images/' . $source->type . '_list.png') }}" />
                </div>
                <div class="source-desc">
                    <h1 class="mt-0 mb-2">{{ $source->getName() }}</h1>
                    <p class="m-0 mb-2">
                        {{ trans('messages.source.intro.' . $source->type) }}
                    </p>
                    <div class="text-muted">
                        {{ trans('messages.source.connected_on', [
                            'date' => \Acelle\Library\Tool::formatDate($source->created_at),
                        ]) }}
                        |
                        <a href="">{{ trans('messages.source.visit_store') }}</a>
                        |
                        <a href="">{{ trans('messages.source.disconnect') }}</a>
                    </div>
                </div>
                <div class="source-action ml-auto">
                    @if (\Acelle\Model\Source::where('id', '!=', $source->id)->count())
                        <div class="dropdown">
                            <button class="btn btn-mc_outline dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            {{ trans('messages.source.switch_store') }}
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                                @foreach (\Acelle\Model\Source::where('id', '!=', $source->id)->get() as $source)
                                    <li><a href="{{ action('SourceController@show', $source->uid) }}">{{ $source->getName() }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="source-stats mt-40">
                <div class="source-desc-line py-4 my-4 d-flex">
                    <div class="desc-icon mr-4 pr-2 d-flex pt-3">
                        <span class="icon-users"></span>
                    </div>
                    <div class="desc">
                        <h5 class="mt-0 mb-1 text-teal-800">{!! trans('messages.source.have_products', [
                            'count' => format_number($source->productsCount()),
                        ]) !!}</h5>
                        <div class="">{{ trans('messages.source.your_store_synchronized') }}</div>
                    </div>
                    <div class="desc-action ml-auto">
                        <a href="{{ action('ProductController@index', [
                            'source_uid' => $source->uid,
                        ]) }}" class="btn btn-mc_default">{{ trans('messages.source.products.manage') }}</a>
                    </div>
                </div>
                <div class="source-desc-line py-4 my-4 d-flex">
                    <div class="desc-icon mr-4 pr-2 d-flex pt-3">
                        <span class="icon-profile"></span>
                    </div>
                    <div class="desc">
                        <h5 class="mt-0 mb-1 text-teal-800">
                            {{ $source->getList()->name }}
                        </h5>
                        <div class="">{{ trans('messages.source.list.synchronized_to_this') }}</div>
                    </div>
                    <div class="desc-action ml-auto">
                        <a href="{{ action('MailListController@overview', $source->mailList->uid) }}" class="btn btn-mc_default">{{ trans('messages.source.products.manage') }}</a>
                    </div>
                </div>
                @if ($automation->getAbandonedCartEmail()->isSetup())
                    <div class="source-desc-line py-4 my-4 d-flex">
                        <div class="desc-icon mr-4 pr-2 d-flex pt-3">
                            <span class="icon-alarm"></span>
                        </div>
                        <div class="desc">
                            <h5 class="mt-0 mb-1 text-teal-800">{{ trans('messages.source.abandoned_cart_email') }}
                                <span class="badge badge-success ml-2">{{ trans('messages.active') }}</span></h5>
                            
                            <div class="">{{ trans('messages.source.abandoned_cart_email.all_setup') }}</div>
                            <div class="email-rates my-4 d-flex">
                                <div class="email-rate mr-4 pr-3">
                                    <div class="rate-value display-4 text-muted">0.0%</div>
                                    <div class="rate-desc text-muted2">{{ trans('messages.rate.opens') }}</div>
                                </div>
                                <div class="email-rate mr-4 pr-3">
                                    <div class="rate-value display-4 text-muted">0.0%</div>
                                    <div class="rate-desc text-muted2">{{ trans('messages.rate.clicks') }}</div>
                                </div>
                                <div class="email-rate mr-4 pr-3">
                                    <div class="rate-value display-4 text-muted">0.0%</div>
                                    <div class="rate-desc text-muted2">{{ trans('messages.rate.send') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="desc-action ml-auto">
                            <a href="javascript:;" class="btn btn-mc_primary launch-automation">{{ trans('messages.edit') }}</a>
                        </div>
                    </div>
                @else
                    <div class="source-desc-line py-4 my-4 d-flex">
                        <div class="desc-icon mr-4 pr-2 d-flex pt-3">
                            <span class="icon-alarm"></span>
                        </div>
                        <div class="desc">
                            <h5 class="mt-0 mb-1 text-teal-800">{{ trans('messages.source.abandoned_cart_email') }}</h5>
                            <div class="">{{ trans('messages.source.abandoned_cart_email.desc') }}</div>
                        </div>
                        <div class="desc-action ml-auto">
                            <a href="javascript:;" class="btn btn-mc_primary launch-automation">{{ trans('messages.source.launch') }}</a>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
    
    <iframe src="{{ action('Automation2Controller@edit', [
        'uid' => $automation->uid,
        'auto_popup' => true,
    ]) }}" id="trans_frame" class="trans_frame" style="display:none"></iframe>
    
    <script>
        var popup = new Popup();

        function jReload() {
            $.ajax({
                method: 'GET',
                url: '',
            })
            .done(function(repsonse) {
                $('.source-stats').html($('<div>').html(repsonse).find('.source-stats').html());
            });
        }
        
        $(document).on('click', '.launch-automation', function(e) {
            e.preventDefault();

            $('.trans_frame').fadeIn();
            
            $('html').css('overflow', 'hidden');
        });
    </script>
@endsection
