<?php

namespace App\Console\Commands;


use App\Models\User;
use App\Models\Booking;
use App\Models\Transaction;

use Eway\Rapid as Eway;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use Config;

class ProcessPaymentsCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:process-payments';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'This command will find bookings where the cancellation cut-off date is today, and will process the booking fee + bond payments if any. (Run once a day @9am)';
    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = Eway::createClient(
            config('eway.api_key'),
            config('eway.api_password'),
            config('eway.endpoint')
        );

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       
        /**
         * Do the Cancellation Cut-off
         * 
         *  - change status to Confirmed or Failed payment
         *  - process transaction
         *  
         */
        $today = $now = Carbon::today();

        $AllActiveBookings = Booking::where('cancellation_cutoff_date', '<=', $today)
                                ->where('status', Booking::$STATUS_ACTIVE)
                                ->get();
        
        if($AllActiveBookings->isEmpty()) {
            return;
        }

        // Group
        $BookingsWithTransaction = [];
        
        foreach($AllActiveBookings as $Booking) {

            // Items with payment
            if($Booking->total > 0 || $Booking->bond > 0) {
                $BookingsWithTransaction[] = $Booking;
            }
            else {
                // Update the status to Confirmed for bookings those have no payment.
                $Booking->status = Booking::$STATUS_CONFIRMED;
                $Booking->save();
            }
        }

        // Nothing to do
        if(!$BookingsWithTransaction) {
            return;
        }


        foreach($BookingsWithTransaction as $booking) {
            
            /**
             * Charge the booking fee first.
             */
            if($booking->total && $booking->total > 0) {

                $transaction = [
                    'Customer' => [
                        'TokenCustomerID' => $booking->user->tokenCustomerID,
                    ],
                    'Payment' => [
                        'TotalAmount' => $booking->total * 100,
                    ],
                    'TransactionType' => \Eway\Rapid\Enum\TransactionType::RECURRING,
                ];

                $Response = $this->client->createTransaction(\Eway\Rapid\Enum\ApiMethod::DIRECT, $transaction);

                $this->_handleEwayResponse($booking, Transaction::$TYPE_BOOKING_FEE, $Response);
            }


            /**
             * Build the security deposit authorization array
             */
            if($booking->bond && $booking->bond > 0) {

                $transaction = [
                    'Customer' => [
                        'TokenCustomerID' => $booking->user->tokenCustomerID,
                    ],
                    'Payment' => [
                        'TotalAmount' => $booking->bond * 100,
                        'InvoiceReference' => ' Security Deposit of '.$booking->getNumber(),
                    ],
                    'TransactionType' => \Eway\Rapid\Enum\TransactionType::RECURRING,
                    'Capture' => false,
                ];

                // Pre-auth the Bond amount
                $Response = $this->client->createTransaction(\Eway\Rapid\Enum\ApiMethod::DIRECT, $transaction);
                $this->_handleEwayResponse($booking, Transaction::$TYPE_BOND, $Response);
            }
        }

        dd('all done');
    }



    /**
     * 
     */
    private function _handleEwayResponse($booking, $transaction_type, $Response) {

        // payment successful
        if( !$Response->getErrors() ) {

            // insert the transaction
            $transaction = Transaction::create([
                'booking_id' => $booking->id,
                'type' => $transaction_type,
                'responseCode' => $Response->ResponseCode,
                'responseMessage' => 'Approved',
                'transactionID' => $Response->TransactionID,
                'transactionStatus' => $Response->TransactionStatus,
                'totalAmount' => $Response->Payment->TotalAmount / 100,
            ]);

            if($Response->TransactionStatus == true) {

                // set the booking status to Confirmed
                $booking->status = Booking::$STATUS_CONFIRMED;
                $booking->save();

                // send confirmed notifications

            }
            else {

                // set the booking status to Confirmed
                $booking->status = Booking::$STATUS_PAYMENT_FAILED;
                $booking->save();

                // send payment failed notification
            }
            return null;

        }
        else {

            // todo: create log, instead of returning the error message
            $errors = array_map(function($error) {
                $replace = [
                    'EWAY_CARDEXPIRYMONTH' => 'Card Expiry Month',
                    'EWAY_CARDEXPIRYYEAR' => 'Card Expiry Year',
                    'EWAY_CARDNUMBER' => 'Card Number',
                    'EWAY_CARDCVN' => ' Card CVN Number'
                ];
                return strtr(Eway::getMessage($error), $replace);
            }, $Response->getErrors());

            dd($errors);

        }

    }


}