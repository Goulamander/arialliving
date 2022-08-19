<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use BladeExtensions;

use App\Mail\AppEmail;
use App\Models\Setting;

class ResetPassword extends AppEmail
{
    use Queueable, SerializesModels;

    private $token;
    private $email;
    private $reset_link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
        $this->reset_link = url('password/reset', ['token' => $this->token]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject_template_code = 'email.templates.reset.password.subject';
        $content_template_code = 'email.templates.reset.password.content';

        $setting_subject = Setting::where('code', $subject_template_code)->first(['replace', 'value']);
        $subject = $setting_subject->value;
        $subject = BladeExtensions::compileString($subject, [
            'reset_link' => $this->reset_link,
            'email' => $this->email
        ]);

        // Build email body
        $setting_content = Setting::where('code', $content_template_code)->first(['replace', 'value']);
        $content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
        $content = BladeExtensions::compileString($content, [
            'reset_link' => $this->reset_link,
            'email' => $this->email
        ]);
        
        return $this->view('email.normal')
                ->with(['content' => $content])
                ->subject($subject);
    }
}
