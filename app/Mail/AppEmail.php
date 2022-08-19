<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AppEmail extends Mailable
{
    /**
     * Set the subject of the message.
     *
     * @param  string  $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
   
}
