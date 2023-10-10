@extends('layouts.clean')



@section('title', trans('messages.create_your_account'))

@section('page_script')    
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/echarts/echarts.js') }}"></script>
    
    <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
@endsection

@section('content')

    <div class="row mt-40">
        <div class="col-md-2"></div>
        <div class="col-md-2 text-right mt-60">
            <div class="auth-brand text-center text-lg-start">
                <div class="auth-logo">
                    <a href="{{url('/')}}" class="logo logo-dark text-center">
                        <span class="logo-lg">
                            <!-- <img src="{{ URL::asset('assetsnew/images/logo-dark.png') }}" alt="" height="22"> -->
                            <img src="https://seed.stagingwebsite.space/wp-content/uploads/2021/10/footer.png" alt="" height="22">
                        </span>
                    </a>

                    <a href="{{url('/')}}" class="logo logo-light text-center">
                        <span class="logo-lg">
                            <!-- <img src="{{ URL::asset('assetsnew/images/logo-light.png') }}" alt="" height="22"> -->
                            <img src="https://seed.stagingwebsite.space/wp-content/uploads/2021/10/footer.png" alt="" >
                        </span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            
            <h1 class="mb-10">{{ trans('messages.email_confirmation') }}</h1>
            <p>{!! trans('messages.activation_email_sent_content') !!}</p>
                
        </div>
        <div class="col-md-1"></div>
    </div>
@endsection
