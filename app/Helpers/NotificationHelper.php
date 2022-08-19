<?php

namespace App\Helpers;

use Carbon\Carbon;


class NotificationHelper
{


	/**
	 * Set the Notification channel based on user setting
	 * - if no setting there the script will return email by default.
	 * 
	 * @param inst $notifiable => User
	 * @param str $setting_key
	 * 
	 * @return array - channels
	 */
	public static function getVia($notifiable, $setting_key)
	{
        \Illuminate\Support\Facades\Log::info("Send mail to: {{$notifiable->email}} with role {{$notifiable->role_id}}");

		// return ['mail'];
		if (!$notifiable || !$setting_key) {
			return ['mail'];
		}
		if ($notifiable->isResident()) {
			return ['mail'];
		}
		if ($notifiable->isResidentVip()) {
			return ['mail'];
		}

		// Read the settings, if provided
		if ($notifiable->settings) {
			$send_via = [];

			// Email
			if (isset($notifiable->settings->notifications_email)) {
				if (in_array($setting_key, explode(',', $notifiable->settings->notifications_email))) {
					$send_via = ['mail'];
				}
			}

			// SMS
			if (isset($notifiable->settings->notifications_sms)) {
				if (in_array($setting_key, explode(',', $notifiable->settings->notifications_sms))) {
					// one more thing. Check the mobile number, if not provided skip
					if ($notifiable->mobile) {
						$send_via = ['nexmo'];
					}
				}
			}

			// Here: add more channels 
			return $send_via;
		}

		// User has no settings yet, by default send Email Notifications only.
		return ;
	}
}
