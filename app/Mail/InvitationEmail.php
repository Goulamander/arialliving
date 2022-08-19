<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use BladeExtensions;

use App\Mail\AppEmail;

class InvitationEmail extends AppEmail
{
    use Queueable, SerializesModels;
    protected $activationLink;
    protected $invited_by;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url, $user, $invited_by = false)
    {
        $this->activationLink = $url;
        $this->user = $user;
        $this->invited_by = $invited_by;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {

        $subject = 'Invitation to get onto Aria Living\'s App.';
                    $content = "
            <h2>Hi {$this->user->first_name},</h2>
            <p>You've been invited to Aria Living's Reservations platform. You can use this to make bookings within your building.</p>
            <p>Just tap the Complete Setup button below to finalise your resident account.</p>
            <p><a href='{$this->activationLink}' class='btn btn-g'>Complete Setup</a></p>  
            <p>If you have any questions, please don’t hesitate to contact us anytime!</p>
            <p>
                <br>Aria Living</br>
                <br>mail@arialiving.com.au</br>
            </p>
            ";

            
        return $this->view('email.normal')
                    ->with(['content' => $content ])
                    ->subject($subject);
    }
}
