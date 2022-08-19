<?php

namespace App\Notifications\Resident;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\NexmoMessage;

use App\Models\Booking;
use App\Models\Setting;

use BladeExtensions;

use App\Mail\NormalEmail;
use App\Helpers\NotificationHelper;

class BookingUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->setting_key = "update_booking";
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return NotificationHelper::getVia($notifiable, $this->setting_key);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
		$subject_template_code = 'email.templates.booking.update.subject';
		$content_template_code = 'email.templates.booking.update.content';

		$setting_subject = Setting::where('code', $subject_template_code)->first(['replace', 'value']);
		$subject = modifyHtmlToBladeCode($setting_subject->replace, $setting_subject->value);
		$subject = BladeExtensions::compileString($subject, [
			'booking' => $this->booking,
		]);

		// Build email body
		$setting_content = Setting::where('code', $content_template_code)->first(['replace', 'value']);
		$content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
		$content = BladeExtensions::compileString($content, [
			'booking' => $this->booking,
			'link'    => ''
		]);

		// No empty emails
		if(!$subject || !$content) {
			return;
        }
        
        return (new NormalEmail($subject, $content))->to($notifiable->email);
    }



    /**
     * Get the Nexmo (SMS) representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toNexmo($notifiable)
    {
        // return (new NexmoMessage)
        //     ->content("")
        //     ->unicode();
    }



    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
