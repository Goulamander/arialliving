<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;

use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\User;
use App\Models\Transaction;

use Illuminate\Encryption\Encrypter;
use Eway\Rapid as Eway;
use Auth;


class TransactionController extends Controller
{

    // eWay client
    protected $client;


    /**
     * Instantiate a new controller instance
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('role:super-admin|building-manager|admin');

        $this->client = Eway::createClient(
            config('eway.api_key'),
            config('eway.api_password'),
            config('eway.endpoint')
        );
    }



    /**
     * Create a refund transaction
     * 
     * @param Request $request
     * @param int $transaction_id
     */
    public function refund(Request $request, $transaction_id) {
        
        $transaction = Transaction::where('id', $transaction_id)->first();

        if(!$transaction) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Transaction']),
                'data' => []
            ], 400);
        }

        // refund amount
        if($request->refund_full_amount == 1) {
            $amount = $transaction->totalAmount;
        }
        else {
            $amount = $request->amount;
        }

        //  build the round array
        $refund = [
            'Refund' => [
                'TransactionID' => $transaction->transactionID,
                'TotalAmount' => $amount * 100
            ],
        ];
        
        $Response = $this->client->refund($refund);

        if( !$Response->getErrors() ) {

            $refund_transaction = Transaction::create([
                'booking_id' => $transaction->booking_id,
                'type' => Transaction::$TYPE_REFUND,
                'responseCode' => $Response->ResponseCode,
                'responseMessage' => $Response->ResponseMessage,
                'transactionID' => $Response->TransactionID,
                'transactionStatus' => $Response->TransactionStatus,
                'totalAmount' => $amount,
                'created_by' => Auth::id(),
                'notes' => $request->input('notes', NULL),
            ]);

			// set the refund transaction id for the original transaction
			if($Response->TransactionStatus == true) {
				$transaction->refund_id = $refund_transaction->id;
				$transaction->save();
			}

			return response()->json([
				'error' => '',
				'message' => __('messages.transaction.refund_success'),
                'data' => [
					'transaction_id' => $refund_transaction->id
				]
            ], 200);
         
        }
        else {

            $errors = array_map(function($error) {
                $replace = [
                    'EWAY_CARDEXPIRYMONTH' => 'Card Expiry Month',
                    'EWAY_CARDEXPIRYYEAR' => 'Card Expiry Year',
                    'EWAY_CARDNUMBER' => 'Card Number',
                    'EWAY_CARDCVN' => ' Card CVN Number'
                ];
                return strtr(Eway::getMessage($error), $replace);
            }, $Response->getErrors());

            return response()->json([
                'error' => $errors,
                'data' => []
            ], 400);
        }
    }



    /**
     * Create a bond release request
	 * 
     * @param Request $request
     * @param int $transaction_id
     */
    public function releaseBond(Request $request, $transaction_id) {

		$transaction = Transaction::where('id', $transaction_id)->first();

        if(!$transaction) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Transaction']),
                'data' => []
            ], 400);
        }

        // Full Bond Release: Use the Cancel transaction request
        if($request->release_full_amount == true) 
        {
			$amount = $transaction->totalAmount;
			$Response = $this->client->cancelTransaction($transaction->transactionID);
		}
		// Partial Bond Release: Use the Capture request
        else {
			$amount = $request->amount;
			$capture = [
				'Payment' => [
					'TotalAmount' => $amount * 100
				],
				'TransactionID' => $transaction->transactionID
			];
			$Response = $this->client->createTransaction(\Eway\Rapid\Enum\ApiMethod::AUTHORISATION, $capture);
        }


        if( !$Response->getErrors() ) {

            $bond_release_transaction = Transaction::create([
                'booking_id' => $transaction->booking_id,
                'type' => $request->release_full_amount ? Transaction::$TYPE_BOND_RELEASE : Transaction::$TYPE_BOND_CAPTURE,
                'responseCode' => $Response->ResponseCode,
                'responseMessage' => $Response->ResponseMessage,
                'transactionID' => $Response->TransactionID,
                'transactionStatus' => $Response->TransactionStatus,
                'totalAmount' => $amount,
                'created_by' => Auth::id(),
                'notes' => $request->input('notes', NULL),
            ]);

			// set the refund transaction id for the original transaction
			if($Response->TransactionStatus == true) {
				$transaction->release_id = $bond_release_transaction->id;
				$transaction->save();
			}

			return response()->json([
				'error' => '',
				'message' => __('messages.transaction.release_success'),
                'data' => [
					'transaction_id' => $bond_release_transaction->id
				]
            ], 200);
         
        }
        else {

            $errors = array_map(function($error) {
                $replace = [
                    'EWAY_CARDEXPIRYMONTH' => 'Card Expiry Month',
                    'EWAY_CARDEXPIRYYEAR' => 'Card Expiry Year',
                    'EWAY_CARDNUMBER' => 'Card Number',
                    'EWAY_CARDCVN' => ' Card CVN Number'
                ];
                return strtr(Eway::getMessage($error), $replace);
            }, $Response->getErrors());

            return response()->json([
                'error' => $errors,
                'data' => []
            ], 400);
        }
    }


	
    /**
     * Retry a failed transaction
	 * 
     * @param int $transaction_id - failed transaction to retry
     * @return Response/Json
     */
    public function retryPayment($transaction_id) {
	   
        $transaction = Transaction::where('id', $transaction_id)
            ->where('transactionStatus', 0)
            ->with('booking:id,user_id,status')
            ->with('booking.user')
            ->first();

        if(!$transaction) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Transaction']),
                'data' => []
            ], 400);
        }

        $direct_transaction = $transaction->booking->user->makeDirectTransaction($transaction->totalAmount);

        if( $direct_transaction['status'] === true ) {

            $new_transaction = Transaction::create([
                'booking_id'        => $transaction->booking->id,
                'type'              => Transaction::$TYPE_DIRECT,
                'responseCode'      => $direct_transaction['response']->ResponseCode,
                'responseMessage'   => Eway::getMessage($direct_transaction['response']->ResponseMessage),
                'transactionID'     => $direct_transaction['response']->TransactionID,
                'transactionStatus' => $direct_transaction['response']->TransactionStatus,
                'totalAmount'       => $transaction->totalAmount,
            ]);

            // Payment Success or Failed?
            $transaction->booking->status = $direct_transaction['response']->TransactionStatus == true ? Booking::$STATUS_CONFIRMED : Booking::$STATUS_PAYMENT_FAILED;
            $transaction->booking->save();

            // Connect the new transaction to its original one.
            $transaction->retry_id = $new_transaction->id;
            $transaction->save();

            return response()->json([
                'error' => '',
                'message' => __('messages.transaction.success'),
                'data' => [
                    'transaction_id' => $new_transaction->id
                ]
            ], 200);

        }
        else {

            return response()->json([
                'error' => $direct_transaction['errors'],
                'data' => []
            ], 400);
        }
        
    }


}
