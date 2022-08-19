

<?php

    /*
    |--------------------------------------------------------------------------
    | Aria Living App messages
    |--------------------------------------------------------------------------
    |
    |
    */

    return [

        // General message templates
        'created' => ':type Created successfully',
        'updated' => ':type Updated successfully',
        'deleted' => ':type Deleted successfully',
        'cloned' => ':type Cloned successfully',

        'notFound' => ':type Not found',
        'cannotSave' => ':type Cannot be saved',
        'noPermission' => 'No permission to execute this action',
        'invalidData' => 'The provided :type is invalid.',

        // Booking
        'deposit_info' => 'Your deposit will be locked on your account until rented item(s) are returned. After returning, your deposit will be released in 2-10 business days depending on your bank.',
        'admin_fee' => 'Administration fees',


        // Booking submission
        'booking' => [
            'date_conflict' => 'Your selected date range isn\'t fully available. Please check your dates. This can also happen if another booking was made before you made yours.',
            'create_error'  => 'Booking could not be created. Please double check your details and try again',
            'update_error'  => 'Booking could not be updated. Please double check your details and try again',
            'update_success' => 'Your booking <b>:booking_id</b> has been updated.',
            'cancel_success' => 'Your booking <b>:booking_id</b> has been cancelled.',
            
            'success'  => 'Thank you for your booking.',
            'payment_note_sub' => 'To secure your booking, please ensure sufficient balance for the transaction.',
        ],

        // Transaction
        'transaction' => [
            'success' => 'Transaction successful',
            'refund_success' => 'Refund successful',
            'release_success' => 'Bond release successful',
        ],

        // Deals
        'deals' => [
            'redeem_limit_reached' => 'Sorry, you have reached the maximum redeem limit of this deal.',
            'redeem_success' => 'Successfully redeemed. Thank you!'
        ],

        // User/Resident
        'user' => [
            'invitation_success' => 'Invitation sent successfully',
            'flagged_success' => 'Resident flagged successfully',
            'unFlagged_success' => 'Resident unflagged successfully',
        ],

        // Building
        'building' => [
            'access_updated' => 'Building access updated'
        ],

        'admin' => [
            'booking' => [
                'cancel_success' => 'Booking <b>:booking_id</b> has been cancelled.',
            ],
            'building' => [

            ]
        ],

    ];
