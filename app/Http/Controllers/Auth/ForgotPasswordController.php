<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Http\Requests\PasswordResetRequest;

use App\Models\Activation;
use App\Services\ActivationService;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    { 

        // Validate the user's reset email address
        $this->validate($request, ['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->where('status', '!=', User::$STATUS_INACTIVE)
            ->first();

        if(!$user) {
            // No account found with this email address
            return back()->withErrors('Sorry. We cannot find your account.');
        }

        // has user even been invited?
        if($user->status == User::$STATUS_INVITED) {

            $activationService = new ActivationService( new Activation() );
            $activationService->sendActivationMail($user);
            
            $request->session()->flash('message.success', 'Your account has not yet been activated. But don\'t worry, we just sent you the activation link.');
            return back();
        }

        // send the reset link and store the response
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            $request->session()->flash('message.success', trans($response));
            return back();
        }

        // If an error was returned by the password broker, we will get this message
        // translated so we can notify a user of the problem. We'll redirect back
        // to where the users came from so they can attempt this process again.
        $message = array('email' => trans($response));
        return back()->withErrors($message);
    }
}
