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
                    <!-- <a href="javascript:void(0);" onclick="openNewWindow();" class="btn pr-0"><img src="{{ url::asset('images/btn_google_signin_light_normal_web@2x.png') }}" alt="login With Gmail" height="auto" width="180px"></a> -->
                    <button class="btn btn-info bg-info-800" id="myBtn">{{ trans('messages.choose') }}</button>
                    <a href="javascript:void(0);" onclick="openNewWindow();"> <span class="server-avatar ">
                                <i class="icon-server"><img src="{{ URL::asset('images/glogo.png') }}" alt="login With Gmail" height="40px" width="40px"></i>
                            </span></a>
                    <h4><a href="javascript:void(0);" onclick="openNewWindow();">Google Workspace/Gmail</a></h4>
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

    <!-- The Modal -->
    <div id="myModal" class="customModal">
        <!-- Modal content -->
        <div class="customModal-content model_popup">
            <div class="top_heading">
            <p style="font-weight: bold;"><img src="{{ URL::asset('images/glogo.png') }}" height="30px" width="30px"> Before Connecting With Google You Need to....</p>
            <span class="close">&times;</span>

        </div>
            <div class="one_connect">
                <h5><img src="{{ URL::asset('images/caution.png') }}" height="30px" width="30px"> One thing before you connect!</h5>
                <p>We're in the process of being verified by Google. In the meantime, you have to do these few steps before
               syncing your inbox. It only takes a few seconds! 
                </p>
            </div>
            <div class="customModal_div">
                <p><span class="customModal-number">1</span> Have your Google Workspace Admin go to 
                <span>
                    <a href="https://admin.google.com/ac/owl/list?tab=configuredApps" target="_blank">App Access Control↗.
                    </a>
                    </span>
                </p>
            </div>
            <div class="customModal_div">
                <p><span class="customModal-number">2</span> Click <span style="font-weight: bold;">"Configure new App"</span>,then <span style="font-weight: bold;">"OAuth App Name or Client ID"</span></p>
            </div>
            <div class="customModal_div">
                <p><span class="customModal-number">3</span> Search for Sende by using our Google App ID <br>
                <span class="your_ad" id="myText">825457320071-9rmp5m2audmgv0ije3uhkn300jr41a0a.apps.googleusercontent.com</span>
                   <a href="#" onclick="copyContent()">
                       <img src="{{ URL::asset('images/clipboards.png') }}" height="auto" width="25px">
                   </a>
                </p>
            </div>
            <div class="customModal_div">
                <p><span class="customModal-number">4</span> Select Sende, mark as <span style="font-weight: bold;">"Trusted"</span> and complete the wizard.</p>
            </div>
            <div class="customModal_div">
                <p><span class="customModal-number">5</span> Then click here to sync your email 
                <img src="{{ URL::asset('images/right-arrow.png') }}" height="auto" width="15px">
                <a href="javascript:void(0);" onclick="openNewWindow();">
                    <img src="{{ URL::asset('images/btn_google_signin.png') }}" height="auto" width="180px">
                </a>
            </p>
            </div>
        </div>
    </div>
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
    <script>
        var modal = document.getElementById("myModal");
        var btn = document.getElementById("myBtn");
        var span = document.getElementsByClassName("close")[0];
        btn.onclick = function() {
            modal.style.display = "block";
        }
        span.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    <script>
        let text = document.getElementById('myText').innerHTML;
        const copyContent = async () => {
            try {
            await navigator.clipboard.writeText(text);
            swal("Content copied to clipboard");
            } catch (err) {
            console.error('Failed to copy: ', err);
            }
        }
    </script>
@endsection