@extends('layouts.backend')

@section('title', $plan->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PlanController@index") }}">{{ trans('messages.plans') }}</a></li>
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
 
    @include('admin.plans._menu')
    <br>
    <form enctype="multipart/form-data" action="{{ action('Admin\PlanController@save', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
        {{ csrf_field() }}
        
        <div class="row">
            <div class="col-md-8">
                <div class="mc_section">
                    <h2>{{ trans('messages.plan.speed_limit') }}</h2>
                        
                    <p>{{ trans('messages.plan.speed_limit.intro') }}</p>
                        
                    <div class="select-custom" data-url="{{ action('Admin\PlanController@sendingLimit', $plan->uid) }}">
                        @include ('admin.plans._sending_limit')
                    </div>
                    <p>{{ trans('messages.plan.process_limit.intro') }}</p>
                    <div class="boxing">
                        <div class="row">
                            <div class="col-md-12">
                                @include('helpers.form_control', ['type' => 'select',
                                    'name' => 'plan[options][max_process]',
                                    'value' => $plan->getOption('max_process'),
                                    'label' => trans('messages.max_number_of_processes'),
                                    'options' => \Acelle\Model\Plan::multiProcessSelectOptions(),
                                    'help_class' => 'plan',
                                    'rules' => $plan->validationRules()['general'],
                                ])
                            </div>
                        </div>
                    </div>
                    <h2 class="text-semibold mt-4">{{ trans('messages.bounce_rate_theshold') }}</h2>
            
                    <p>{!! trans('messages.bounce_rate_theshold.wording') !!}</p>

                    @include('helpers.form_control', ['type' => 'select',
                        'name' => 'plan[options][bounce_rate_theshold]',
                        'value' => $plan->getOption('bounce_rate_theshold'),
                        'label' => trans('messages.bounce_rate_theshold.set_a_limit'),
                        'options' => \Acelle\Model\Plan::bounceRateThesholdOptions(),
                        'help_class' => 'plan',
                        'rules' => $plan->validationRules()['general'],
                    ])
                    <hr>
                    <button class="btn bg-info-800 mr-10">{{ trans('messages.save') }}</button>
                    <a href="{{ action('Admin\PlanController@index') }}" type="button" class="btn bg-grey-600">
                        {{ trans('messages.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

@endsection
