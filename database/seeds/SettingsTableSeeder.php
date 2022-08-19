<?php

use Illuminate\Database\Seeder;

use Carbon\Carbon;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();


        $settings = array (
			// Booking reminder Subject
			array (
				'code' => 'email.templates.booking.reminder.subject',
				'label' => 'Booking Reminder - Subject',
				'value' => 'Booking Reminder - [booking_number]',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'text',
				'replace' => '[{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
			// Booking reminder Content
			array (
				'code' => 'email.templates.booking.reminder.content',
				'label' => 'Booking Reminder - Content',
				'value' => 'Hi [name], this is a friendly reminder of your [booking_item] booking on [booking_date] at [booking_start].',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$booking->building->name}}"}, 
					{"html":"[building_manager_name]","blade":"{{$booking->building->contact_name}}"}, 
					{"html":"[building_manager_email]","blade":"{{$booking->building->email}}"}, 
					{"html":"[building_manager_phone]","blade":"{{$booking->building->phone}}"}, 
					{"html":"[name]","blade":"{{$booking->user->first_name}}"},
					{"html":"[booking_item]","blade":"{{$booking->bookableItem->title}}"}, 
					{"html":"[booking_date]","blade":"{{dateFormat($booking->start)}}"}, 
					{"html":"[booking_start]","blade":"{{timeFormat($booking->start)}}"}, 
					{"html":"[link]","blade":"{{$link}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),

			// Booking Confirmation Subject
			array (
				'code' => 'email.templates.booking.confirmation.subject',
				'label' => 'Booking Confirmation - Subject',
				'value' => 'Booking Confirmation - [booking_number]',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'text',
				'replace' => '[{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
			// Booking Confirmation Content
			array (
				'code' => 'email.templates.booking.confirmation.content',
				'label' => 'Booking Confirmation - Content',
				'value' => 'Hi [name],<br>
					<br>
					<p>thank you for your booking, here are your booking details:</p>
					<br>
					[booking_details]
					<br>
					<br>
					<strong>Booking Instructions:</strong>
					<br>
					[booking_instructions]',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$booking->building->name}}"}, 
					{"html":"[building_manager_name]","blade":"{{$booking->building->contact_name}}"}, 
					{"html":"[building_manager_email]","blade":"{{$booking->building->email}}"}, 
					{"html":"[building_manager_phone]","blade":"{{$booking->building->phone}}"}, 
					{"html":"[name]","blade":"{{$booking->user->first_name}}"}, 
					{"html":"[booking_details]","blade":"{!!$booking->getBookingDetails()!!}"}, 
					{"html":"[booking_instructions]","blade":"{!!$booking->getBookingInstructions()!!}"},
					{"html":"[link]","blade":"{{$link}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),

			// Booking Update Subject
			array (
				'code' => 'email.templates.booking.update.subject',
				'label' => 'Booking Update - Subject',
				'value' => 'Booking Update Notification - [booking_number]',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'text',
				'replace' => '[{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
			// Booking Update Content
			array (
				'code' => 'email.templates.booking.update.content',
				'label' => 'Booking Update - Content',
				'value' => 'Hi [name],<br>
					<br>
					<p>your booking has been updated, please see the booking details below:</p>
					<br>
					[booking_details]
					<br>
					<p><strong>Booking Instructions:</strong></p>
					[booking_instructions]
				',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$booking->building->name}}"}, 
					{"html":"[building_manager_name]","blade":"{{$booking->building->contact_name}}"}, 
					{"html":"[building_manager_email]","blade":"{{$booking->building->email}}"}, 
					{"html":"[building_manager_phone]","blade":"{{$booking->building->phone}}"}, 
					{"html":"[name]","blade":"{{$booking->user->first_name}}"}, 
					{"html":"[booking_details]","blade":"{!!$booking->getBookingDetails()!!}"}, 
					{"html":"[booking_instructions]","blade":"{!!$booking->getBookingInstructions()!!}"},
					{"html":"[link]","blade":"{{$link}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),

			// Booking Cancellation Subject
			array (
				'code' => 'email.templates.booking.cancellation.subject',
				'label' => 'Booking Cancellation - Subject',
				'value' => 'Booking Cancellation - [booking_number]',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'text',
				'replace' => '[{"html":"[booking_number]","blade":"{{$booking->getNumber()}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
			// Booking Cancellation Content
			array (
				'code' => 'email.templates.booking.cancellation.content',
				'label' => 'Booking Cancellation - Content',
				'value' => 'Hi [name],<br>
					<br>
					<p>your booking with the following details has been cancelled:</p>
					<br>
					[booking_details]',
				'group' => 'Email',
				'sub_group' => 'Booking Templates',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$booking->building->name}}"}, 
					{"html":"[building_manager_name]","blade":"{{$booking->building->contact_name}}"}, 
					{"html":"[building_manager_email]","blade":"{{$booking->building->email}}"}, 
					{"html":"[building_manager_phone]","blade":"{{$booking->building->phone}}"}, 
					{"html":"[name]","blade":"{{$booking->user->first_name}}"}, 
					{"html":"[booking_details]","blade":"{!!$booking->getBookingDetails()!!}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
			// Residents Invite Subject
			array (
				'code' => 'email.templates.invite.residents.subject',
				'label' => 'Subject',
				'value' => 'Welcome to Aria Living Online! (New Template)',
				'group' => 'Email',
				'sub_group' => 'Residents Invite',
				'type' => 'text',
				'replace' => '',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
			// Residents Invite Content
			array (
				'code' => 'email.templates.invite.residents.content',
				'label' => 'Content',
				'value' => '<p>Hi [first_name], (New Template)</p><p><br></p><p>You\'ve been invited to Aria Living\'s Reservations platform. You can use this to book the private dining room, Free PT &amp; Yoga as well as any events that are happening within [building_name]!</p><p>Just tap the Complete Setup button below to finalise your resident account.</p><p><br></p><p><a href="[activation_link]" rel="noopener noreferrer" target="_blank">Complete Setup</a></p><p><br></p><p><br></p>',
				'group' => 'Email',
				'sub_group' => 'Residents Invite',
				'type' => 'textarea',
				'replace' => '[{"html":"[building_name]","blade":"{{$user->building->name}}"},
					{"html":"[first_name]","blade":"{{$user->first_name}}"},
					{"html":"[activation_link]","blade":"{{$activationLink}}"}]',
				'order' => 0,
				'created_at' => '2020-01-01 00:00:00',
				'updated_at' => '2020-01-01 00:00:00'
			),
        );

		// insert the settings
		foreach($settings as $setting) {
			Setting::insert($setting);
		}
       
    }

}