<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use App\Models\Activation;
use App\Models\User;
use App\Mail\ActivationMail;
use App\Mail\InvitationEmail;
use Illuminate\Support\Facades\Mail;


class ActivationService
{
    protected $activation;

    public function __construct(Activation $activation) {
        $this->activation = $activation;
    }

    /**
     * Sends an activation email to the user with a unique URL after
     * creating an Activation object.
     *
     * @param User
     * @return void
     */
    public function sendActivationMail(User $user) {

        // Check if the user is already activated
        if ($user->activated) { 
            return; 
        }

        $this->activation->createActivation($user);

        // The activation url string to be sent by email
        $url = route('user.activate', ['token' => $this->activation->token]);

      
        // Create a new ActivationMail
        $mail = new ActivationMail($url, $user);

        // Send
        Mail::to($user->email)->queue($mail);

    }



    /**
     * Activate the user account and delete the Activation object.
     *
     * @return void
     */
    public function activateUser(User $user) {
        // Activate user and save
        $user->activated = true;
        $user->save();
    }
}
