@extends('layouts.clean_auth')

@section('title', trans('messages.password_reset'))

@section('content')

    
    <!-- send reset password email -->
    <form class="" role="form" method="POST" action="{{ url('/password/email') }}">
        {{ csrf_field() }}
        
        <div class="panel panel-body">                        
            
            @if (session('status'))
                <div class="alert alert-success">
        {{ session('status') }}
                </div>
            @endif
            
            <h4 class="text-semibold mt-0">{{ trans('messages.password_reset') }}</h4>
            <p>{{ trans('messages.password_reset.help') }}</p>
            
            <div class="form-group mb-3 has-feedback has-feedback-left{{ $errors->has('email') ? ' has-error' : '' }}">
                <label>{{ trans('messages.enter_your_registered_email_here') }}</label>
                <input id="email" type="email" class="form-control" name="email" placeholder="{{ trans("messages.email") }}" value="{{ old('email') }}">
                <div class="form-control-feedback has-label">
        <i class="icon-envelop5 text-muted"></i>
                </div>
                @if ($errors->has('email'))
        <span class="help-block">
            <strong>{{ $errors->first('email') }}</strong>
        </span>
                @endif                            
            </div>
            
            <button type="submit" class="btn btn-lg bg-teal btn-block mb-3">
                {{ trans('messages.send_password_reset_link') }}  <i class="icon-circle-right2 position-right"></i>
            </button>
            <div class="back_to_login">
            <a href="{{ url("/login") }}" class="btn-lg btn-block  text-semibold" style="color: #fff !important;">
                {{ trans("messages.return_to_login") }}
            </a>
        </div>
            
        </div>
    </form>
    <!-- /send reset password email -->  
     <!-- Footer-->
                        <footer class="footer footer-alt">
                            <p class="text-muted">Don't have an account? <a href="{{url('users/register')}}" class="text-muted ms-1"><b>Sign Up</b></a></p>
                        </footer>

                    
@endsection



