@extends('layouts.frontend')

@section('title', trans('messages.sending_servers'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title listing-form row">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ url("/") }}">{{ trans('messages.home') }}</a></li>
            <li class="active">Sending Servers</li>
        </ul>

        <h1>
            <span class="text-semibold"><i class="icon-plus2"></i> {{ trans('messages.select_sending_servers_type') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row listing-form" >
        <div class="col-md-1"></div>
        <div class="col-md-10 mb-3">
            <ul class="modern-listing big-icon no-top-border-list mt-0">

                @foreach (Auth::user()->customer->getSendingServertypes() as $key => $type)
                    <li>
                        <a href="{{ action('SendingServerController@create', ["type" => $key]) }}" class="btn btn-info bg-info-800">{{ trans('messages.choose') }}</a>
                        <a href="{{ action('SendingServerController@create', ["type" => $key]) }}">
                            <span class="server-avatar server-avatar-{{ $key }}">
                                <i class="icon-server"></i>
                            </span>
                        </a>
                        <h4><a href="{{ action('SendingServerController@create', ["type" => $key]) }}">{{ trans('messages.' . $key) }}</a></h4>
                        <p>
                            {{ trans('messages.sending_server_intro_' . $key) }}
                        </p>
                    </li>

                @endforeach
                <li>
                    <a href="javascript:void(0);" onclick="openNewWindow();" class="btn pr-0"><img src="{{ URL::asset('images/btn_google_signin_light_normal_web@2x.png') }}" alt="login With Gmail" height="auto" width="180px"></a>
                    <a href="javascript:void(0);" onclick="openNewWindow();"> <span class="server-avatar ">
                                <i class="icon-server"><img src="{{ URL::asset('images/glogo.png') }}" alt="login With Gmail" height="40px" width="40px"></i>
                            </span></a>
                    <h4><a href="javascript:void(0);" onclick="openNewWindow();">Gmail</a></h4>
                    <p>Send emails through your gmail account</p>
                   
                </li>

                <li>
                    <a href="javascript:void(0);" onclick="openNewWindow1();" class="btn btn-info bg-info-800"> {{ trans('messages.choose') }}</a>
                    <a href="javascript:void(0);" onclick="openNewWindow1();"> <span class="server-avatar ">
                                <i class="icon-server"><img src="{{ URL::asset('images/outlook_logo.png') }}" alt="login With Gmail" height="40px" width="40px"></i>
                            </span></a>
                    <h4><a href="javascript:void(0);" onclick="openNewWindow1();">Microsoft</a></h4>
                    <p>Send emails through your Microsoft account</p>
                   
                </li>

            </ul>
            <div class="pull-right">
                <a href="{{ action('SendingServerController@index') }}" type="button" class="btn bg-grey">
                    <i class="icon-cross2"></i> {{ trans('messages.cancel') }}
                </a>
            </div>
        </div>
        <div class="col-md-1"></div>
    </div>
    <script type="text/javascript">
        function openNewWindow(){
            urlData =  "{{ url('oauth/gmail') }}";
            window.open(urlData);
        }

        function openNewWindow1(){
            urlData =  "{{ url('automation/graphLogin') }}";
            window.open(urlData);
        }
    </script>
@endsection
