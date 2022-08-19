<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use BladeExtensions;

use App\Mail\AppEmail;
use App\Models\Setting;

class ActivationMail extends AppEmail
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
    public function build()
    {

        // Resident invitation
        if( $this->user->isResident() ) {

            $subject_template_code = 'email.templates.invite.residents.subject';
            $content_template_code = 'email.templates.invite.residents.content';

            $setting_subject = Setting::where('code', $subject_template_code)->first(['replace', 'value']);
            $subject = modifyHtmlToBladeCode($setting_subject->replace, $setting_subject->value);
            $subject = BladeExtensions::compileString($subject, [
                'user' => $this->user,
                'activation_link' => $this->activationLink
            ]);

            // Build email body
            $setting_content = Setting::where('code', $content_template_code)->first(['replace', 'value']);
            $content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
            $content = BladeExtensions::compileString($content, [
                'user' => $this->user,
                'activation_link' => $this->activationLink
		    ]);
        }
        // User invitation
        else {
            $subject_template_code = 'email.templates.invite.admin.subject';
            $content_template_code = 'email.templates.invite.admin.content';

            $setting_subject = Setting::where('code', $subject_template_code)->first(['replace', 'value']);
            $subject = $setting_subject->value;
            $subject = BladeExtensions::compileString($subject, [
                'user' => $this->user,
                'activationLink' => $this->activationLink
            ]);

            // Build email body
            $setting_content = Setting::where('code', $content_template_code)->first(['replace', 'value']);
            $content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
            $content = BladeExtensions::compileString($content, [
                'user' => $this->user,
                'activationLink' => $this->activationLink
		    ]);    
        }

        return $this->view('email.normal')
                    ->with(['content' => $content])
                    ->subject($subject);
    }
}
