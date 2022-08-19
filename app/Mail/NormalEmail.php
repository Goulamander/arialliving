<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use BladeExtensions;
use Illuminate\Mail\Mailable;


class NormalEmail extends Mailable implements ShouldQueue
{

    use Queueable, SerializesModels;
    
    public $subject;
    protected $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subj, $content)
    {
        $this->subject = $subj;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.normal')
                    ->with(['content' => $this->content])
                    ->subject($this->subject);
    }
}