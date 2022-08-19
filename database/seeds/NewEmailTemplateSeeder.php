<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Carbon\Carbon;
class NewEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();
        $templates = array (
			// Reset Password Subject
			array (
				'code' => 'email.templates.reset.password.subject',
				'label' => 'Subject',
				'value' => 'Aria Living - Password reset',
				'group' => 'Email',
				'sub_group' => 'Reset Password',
				'type' => 'text',
				'replace' => '',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
			// Reset Password Content
			array (
				'code' => 'email.templates.reset.password.content',
				'label' => 'Content',
				'value' => ' <p>A password reset request was initiated for the [email] account with Aria Living. Click below to reset your password.</p><p><a href="[reset_link]" rel="noopener noreferrer" target="_blank">Reset Password</a></p>',
				'group' => 'Email',
				'sub_group' => 'Reset Password',
				'type' => 'textarea',
				'replace' => '[{"html":"[email]","blade":"{{$email}}"},
					{"html":"[reset_link]","blade":"{{$reset_link}}"}]',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
            // Admin Invite Template Subject
			array (
				'code' => 'email.templates.invite.admin.subject',
				'label' => 'Subject',
				'value' => 'Admin Account Setup for Aria Living Reservations',
				'group' => 'Admin',
				'sub_group' => 'Admins Invite',
				'type' => 'text',
				'replace' => '',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
			// Admin Invite Content
			array (
				'code' => 'email.templates.invite.admin.content',
				'label' => 'Content',
				'value' => '<h2>Hi [first_name]</h2>
				<p>You\'ve been invited to Aria Living\'s Reservations platform. Just tap the Complete Setup button below to finalise your admin account.</p><p><a href="[activation_link]"  class="btn btn-g">Complete Setup</a></p>',
				'group' => 'Admin',
				'sub_group' => 'Admins Invite',
				'type' => 'textarea',
				'replace' => '[{"html":"[first_name]","blade":"{{$user->first_name}}"},
					{"html":"[activation_link]","blade":"{{$activationLink}}"}]',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
            // New Booking Subject
			array (
				'code' => 'email.templates.new.booking.subject',
				'label' => 'New Booking Notification - Subject',
				'value' => 'New Booking - [booking_number]',
				'group' => 'Admin',
				'sub_group' => 'New Booking Notification',
				'type' => 'text',
				'replace' => '[{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"}]',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
			// New Booking Content
			array (
				'code' => 'email.templates.new.booking.content',
				'label' => 'New Booking Notification - Content',
				'value' => '<h3>A new booking (No. [booking_number]) has come in at [building_name]</h3>
                <br>
				<p>See details:</p>
                <br>
                <p>[booking_details]</p>',
				'group' => 'Admin',
				'sub_group' => 'New Booking Notification',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$booking->building->name}}"}, 
					{"html":"[building_manager_name]","blade":"{{$booking->building->contact_name}}"}, 
					{"html":"[building_manager_email]","blade":"{{$booking->building->email}}"}, 
					{"html":"[building_manager_phone]","blade":"{{$booking->building->phone}}"}, 
					{"html":"[name]","blade":"{{$booking->user->first_name}}"}, 
					{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"},
					{"html":"[booking_details]","blade":"{!!$booking->getBookingDetails()!!}"}]',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
            // Update Booking Subject
			array (
				'code' => 'email.templates.update.booking.subject',
				'label' => 'Booking Update Notification - Subject',
				'value' => 'Booking Changes - [booking_number]',
				'group' => 'Admin',
				'sub_group' => 'Booking Update Notification',
				'type' => 'text',
				'replace' => '[{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"}]',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			),
			// Update Booking Content
			array (
				'code' => 'email.templates.update.booking.content',
				'label' => 'Booking Update Notification - Content',
				'value' => '<h3>A booking (No. [booking_number])has changed at [building_name]</h3>
                <br>
				<p>See details:</p>
                <br>
                <p>[booking_details]</p>',
				'group' => 'Admin',
				'sub_group' => 'Booking Update Notification',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$booking->building->name}}"}, 
					{"html":"[building_manager_name]","blade":"{{$booking->building->contact_name}}"}, 
					{"html":"[building_manager_email]","blade":"{{$booking->building->email}}"}, 
					{"html":"[building_manager_phone]","blade":"{{$booking->building->phone}}"}, 
					{"html":"[name]","blade":"{{$booking->user->first_name}}"}, 
					{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"},
					{"html":"[booking_details]","blade":"{!!$booking->getBookingDetails()!!}"}]',
				'order' => 0,
				'created_at' => $now,
				'updated_at' => $now
			)
        );

		// insert the settings
		foreach($templates as $template) {
			Setting::insert($template);
		}
    }
}
