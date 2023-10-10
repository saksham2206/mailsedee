@extends('layouts.clean_auth')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<style type="text/css">

    .select2{
        width: 100% !important;
        margin-top: 20px;
    }
    .hide{
        display: none !important;
    }
    lable{
        font-weight: 400px !important;
        color: rgb(140, 152, 165) !important;
    }
</style>
            

                        <!-- title-->
                        <!-- <h4 class="mt-0">Sign Up</h4>
                        <p class="text-muted mb-4">Don't have an account? Create your account, it takes less than a minute</p> -->

                        <!-- form -->
                         <form enctype="multipart/form-data" action="{{ action('UserController@register') }}" method="POST" class="form-validate-jqueryz subscription-form">
        {{ csrf_field() }}
        <div class="row  mc-form">
            
            
                
                <h1 class="mb-20">{{ trans('messages.create_your_account') }}</h1>
                <p>{!! trans('messages.register.intro', [
                    'login' => url("/login"),
                ]) !!}</p>
                <div class="become"><p style="margin-bottom: 20px;margin-top: 0px;">Become a member of the SENDE family and grow your business with us!</p></div>
                    
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'email',
                    'placeholder' => 'Enter your email',
                    'value' => $user->email,
                    'help_class' => 'profile',
                    'rules' => $user->registerRules()
                ])
                
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'first_name',
                    'placeholder' => 'Enter your first name',
                    'value' => $user->first_name,
                    'rules' => $user->registerRules()
                ])
                
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'last_name',
                    'placeholder' => 'Enter your last name',
                    'value' => $user->last_name,
                    'rules' => $user->registerRules()
                ])
                
                <div class="form-group control-text mb-3">
                    
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group input-group-merge">
                       <input id="password" type="password" class="form-control" name="password" placeholder="Password" 
                                                        value="{{ isset(\Acelle\Model\User::getAuthenticateFromFile()['password']) ? \Acelle\Model\User::getAuthenticateFromFile()['password'] : '' }}">
                        <div class="input-group-text" data-password="false">
                            <span class="password-eye"></span>
                        </div>
                    </div>
                </div>
                
                @include('helpers.form_control', [
                    'type' => 'select',
                    'name' => 'timezone',
                    'value' => $customer->timezone,
                    'options' => Tool::getTimezoneSelectOptions(),
                    'include_blank' => trans('messages.choose'),
                    'rules' => $user->registerRules()
                ])                              
                
                @include('helpers.form_control', [
                    'type' => 'text',
                    'name' => 'language_id',
                    'value' => 1,
                    'class' =>'hide',
                    'rules' => $user->registerRules()
                ])
                
                @if (Acelle\Model\Setting::get('registration_recaptcha') == 'yes')
                    <div class="row" style="margin-top:20px; ;">
                        
                        <div class="col-md-6">
                            @if ($errors->has('recaptcha_invalid'))
                                <div class="text-danger text-center">{{ $errors->first('recaptcha_invalid') }}</div>
                            @endif
                            {!! Acelle\Library\Tool::showReCaptcha() !!}
                        </div>
                    </div>
                @endif
                <div class="become"><p style="margin-bottom: 10px;margin-top: 10px;">Are you ready to SENDE and gain results?</p></div>
                <div class="text-center d-grid"  style="margin-top:20px; margin-bottom: 20px;">
                  
                        <button type='submit' class="btn btn-mc_primary res-button"><!-- <i class="icon-check"></i> --> {{ trans('messages.get_started') }}</button>
                   
                    <div class="col-md-12 mt-3">
                        {!! trans('messages.register.agreement_intro') !!}
                    </div>
                        
                </div>
                <div class="row flex align-items">
                <div class="col-md-12 text-center">
                    <p class="text-muted">Already have account? <a href="{{route('login')}}" class="text-muted ms-1"><b>Log In</b></a></p>
                </div>
                </div>
            
        </div>
    </form>

    <script>
        @if (isSiteDemo())
            $('.res-button').click(function(e) {
                e.preventDefault();

                notify('notice', '{{ trans('messages.notify.notice') }}', '{{ trans('messages.operation_not_allowed_in_demo') }}');
            });
        @endif
        @if (request()->session()->get('user-reg'))
        //alert('hi');
        $(document).ready(function() {
            // Success alert
            swal({
                title: "We need to confirm your email address to complete the registration process. To complete the registration process, please click the link in the email we just sent you.",
                text: "",
                confirmButtonColor: "#00695C",
                type: "warning",
                allowOutsideClick: true,
                confirmButtonText: "{{ trans('messages.ok') }}",
                customClass: "swl-warning",
                html:true
            });

        });
        <?php request()->session()->forget('user-reg'); ?>
    @endif
    </script>
                        <!-- end form-->

                        <!-- Footer-->
                        <!-- <div class="row flex align-items">
                        <footer class="col-md-12 footer footer-alt">
                            <p class="text-muted">Already have account? <a href="{{route('login')}}" class="text-muted ms-1"><b>Log In</b></a></p>
                        </footer>
                        </div> -->

                    
@endsection
