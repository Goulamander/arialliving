<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Lang;
use App\Services\ActivationService;
use Illuminate\Http\Request;

use App\Models\User;
use Auth;

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
     * An instance of Activation Service
     *
     * @var \App\Services\ActivationService
     */
    protected $activationService;


    /**
     * Create a new controller instance.
     *
     * @param  App\Services\ActivationService
     * @return void
     */
     public function __construct(ActivationService $activationService)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->activationService = $activationService;
    }

    /**
     * Checks if the user was activated before login.
     *
     * @param  \Illuminate\Http\Request
     * @param  \App\Models\User
     * @return \Illuminate\Http\Response
     */
    protected function authenticated(Request $request, $user)
    {

        // User is inactive
        if($user->status == User::$STATUS_INACTIVE) {
            Auth::logout();
            return redirect('/login')->withErrors('Sorry. We cannot find your account');
        }

        // Check if the user who is trying to log in is activated
        if ($user->status == User::$STATUS_INVITED) {
            Auth::logout();           
            $this->activationService->sendActivationMail($user);
            // return redirect('/login')->withErrors($this->message('Your account hasn\'t been activated. Please visit you email account fo the activation link.'));
            return redirect('/login')->withErrors('Your account hasn\'t been activated. Please visit your email account for the activation link.');
        }

        // residents go the the front-end.
        if( $user->isResident() ) {
            return redirect()->intended($this->redirectPath());
        }

        // admins go the the back-end.
        return redirect('/admin');
    }
    

}
