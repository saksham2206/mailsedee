<?php

namespace Acelle\Http\Controllers\Auth;

use Acelle\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Acelle\Model\Setting;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }
    
    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(\Illuminate\Http\Request $request)
    {

        $rules = [
            $this->username() => 'required',
            'password' => 'required'
        ];

        if (Setting::isYes('login_recaptcha') && !Setting::isYes('theme.beta')) {
            if (!\Acelle\Library\Tool::checkReCaptcha($request)) {
                $rules['recaptcha_invalid'] = 'required';
            }
        }
        // dd($request);
        $this->validate($request, $rules);
        //dd($request);
    }

    public function authenticated($request, $user)
    {

        // If user is not activated
        if (!$user->activated) {
            $uid = $user->uid;
            auth()->logout();
            request()->session()->put('user-not-activated',$uid);
            return redirect('login');
        }
        
        return redirect()->intended('/');
    }
}
