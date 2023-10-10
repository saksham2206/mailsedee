@extends('layouts.clean_auth')

@section('title', trans('messages.login'))

@section('content')

       
            

                        <!-- title-->
                        <h4 class="mt-0">Sign In</h4>
                        <p class="text-muted mb-4">Enter your email address and password to access account.</p>

                        <!-- form -->
                        <form role="form"  method="POST" action="{{ url('/login') }}">
                             {{ csrf_field() }}
                            <div class="mb-3">
                                <label for="emailaddress" class="form-label">Email address</label>
                                    <input id="email" type="email" class="form-control" name="email" placeholder="{{ trans("messages.email") }}"
                                        value="{{ old('email') ? old('email') : (isset(\Acelle\Model\User::getAuthenticateFromFile()['email']) ? \Acelle\Model\User::getAuthenticateFromFile()['email'] : "") }}">
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        {{ $errors->first('email') }}
                                    </span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <a href="{{ url('/password/reset') }}" class="text-muted float-end"><small>Forgot your password?</small></a>
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group input-group-merge">
                                   <input id="password" type="password" class="form-control" name="password" placeholder="{{ trans("messages.password") }}"
                                                                    value="{{ isset(\Acelle\Model\User::getAuthenticateFromFile()['password']) ? \Acelle\Model\User::getAuthenticateFromFile()['password'] : '' }}">
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        {{ $errors->first('password') }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input1" id="checkbox-signin"  name="remember">
                                    <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                </div>
                            </div>
                            @if (Acelle\Model\Setting::get('login_recaptcha') == 'yes')
                                <div class="row" style="margin-top:20px; ;">
                                    
                                    <div class="col-md-6">
                                        @if ($errors->has('recaptcha_invalid'))
                                            <div class="text-danger text-center">{{ $errors->first('recaptcha_invalid') }}</div>
                                        @endif
                                        {!! Acelle\Library\Tool::showReCaptcha() !!}
                                    </div>
                                </div>
                            @endif
                            <div class="text-center d-grid">
                                <button class="btn btn-primary login-button" type="submit">Log In </button>
                            </div>
                            <!-- social-->
                          <!--   <div class="text-center mt-4">
                                <p class="text-muted font-16">Sign in with</p>
                                <ul class="social-list list-inline mt-3">
                                    <li class="list-inline-item">
                                        <a href="javascript: void(0);" class="social-list-item border-primary text-primary"><i class="mdi mdi-facebook"></i></a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="javascript: void(0);" class="social-list-item border-danger text-danger"><i class="mdi mdi-google"></i></a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="javascript: void(0);" class="social-list-item border-info text-info"><i class="mdi mdi-twitter"></i></a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="javascript: void(0);" class="social-list-item border-secondary text-secondary"><i class="mdi mdi-github"></i></a>
                                    </li>
                                </ul>
                            </div> -->
                        </form>
                        <!-- end form-->

                        <!-- Footer-->
                        <footer class="footer footer-alt">
                            <p class="text-muted">Don't have an account? <a href="{{url('users/register')}}" class="text-muted ms-1"><b>Sign Up</b></a></p>
                        </footer>

                    
@endsection
