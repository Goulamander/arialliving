<?php

namespace App\Notifications\Admin;

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

class BookingCreatedAdmin extends Notification implements ShouldQueue
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
        // $this->setting_key = "new_booking";
        $this->setting_key = "new_order";
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
        // Build email subject
        $setting_subject = Setting::where('code', 'email.templates.new.booking.subject')->first(['replace', 'value']);
        $subject = modifyHtmlToBladeCode($setting_subject->replace, $setting_subject->value);
        $subject = BladeExtensions::compileString($subject, [
            'booking' => $this->booking,
        ]);

        // Build email body
        $setting_content = Setting::where('code', 'email.templates.new.booking.content')->first(['replace', 'value']);
        $content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
        $content = BladeExtensions::compileString($content, [
            'booking' => $this->booking,
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

        $content = "New Booking @{$this->booking->building->name}\nBooking No: ".$this->booking->getNumber()."\n".$this->booking->getBookingDetails(false);
        
        return (new NexmoMessage)
            ->content($content)
            ->unicode();

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
