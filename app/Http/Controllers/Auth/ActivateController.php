<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseClasses\Controller;
use App\Services\ActivationService;
use App\Models\Activation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\SetPasswordRequest;
use Illuminate\Support\Facades\Artisan;
use Auth;

class ActivateController extends Controller
{
     /**
      * Activate a registered user after they navigated to the activation
      * url sent to their email.
      *
      * @param string $token The token in the activation url
      * @return Response
      */
     public function activateUser(ActivationService $activationService, $token) {


        // Find the Activation object with the token in the URL
        $activation = Activation::activationByToken($token)->first();

        // If the activation object doesn't exit, the user has already been activated.
        if (!$activation) {
            return redirect('/login')->withErrors($this->message('activationWarning'));
        }

        // If the activation object exists, find the user and activate
        $user = User::find($activation->user_id);

        if( !$user->is_set_password ) {
            //  User needs to set a password first
            return view('auth.setPassword')->with(compact('user', 'token'));
        }
        else {
            //  Activate user's account
            $activationService->activateUser($user);
            $activation->deleteActivation();
            //
            return redirect('/login')->with($this->message('activationSuccessCustomer'));
        }
        exit;

     }

     /**
      * Set password for invited user
      *
      * @param App\Http\Requests\SetPasswordRequest $request
      * @param string $token - The token in the activation url
      *
      * @return Response
      */
     public function setPassword(SetPasswordRequest $request, $token) {
        
        $activation = Activation::activationByToken($token)->first();
        $user = User::find($activation->user_id);

        // save the password
        $user->password = bcrypt($request->input('password'));
        $user->is_set_password = 1;

        $user->activated = 1; // inheritance only... now we use status instead.

        $user->status = User::$STATUS_ACTIVE;
        $user->save();

        Auth::login($user, true);

        if( $user->isResident() ) {
            return redirect('/login')
                ->with($this->message('activationSuccessUser'));
        }

        // admins go the the back-end.
        return redirect('/admin');
     }



     /**
      * A utility method that returns different messages to be displayed to
      * the user.
      *
      * @param string $messageName  The name of the message to be returned
      * @return string The message
      */
     protected function message($messageName)
     {
         $messages = array(
             'activationWarning' => 'You have already activated this account',
             'activationSuccessCustomer' => 'Account activated successfully!',
             'activationSuccessUser' => 'Welcome on board. Your account has been successfully activated!'
         );
         return [$messageName => $messages[$messageName]];
     }
}
