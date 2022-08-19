<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'booking_id',
        'type',
        'responseCode',
        'responseMessage',
        'transactionID',
        'transactionStatus',
        'totalAmount',
        'created_by',
        'notes',
        'refund_id',
        'release_id',
        'retry_id'
    ];


    public static $TYPE_BOOKING_FEE = 1;
    public static $TYPE_BOND = 2;
    public static $TYPE_REFUND = 3;
    public static $TYPE_BOND_RELEASE = 4;
    public static $TYPE_BOND_CAPTURE = 5;
    public static $TYPE_DIRECT = 6;


    /***********************************************************************/
    /************************  ELOQUENT RELATIONSHIPS  **********************/
    /***********************************************************************/

    /**
     * Booking
     * @return App\Models\Booking
     */
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id');
    }


    /**
     * Created by User 
     * @return App\Models\User
     */
    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }


    /**
     * Get the refound transaction of this transaction
     * @return App\Models\User
     */
    public function refundTransaction()
    {
        return $this->hasOne('App\Models\Transaction', 'id', 'refund_id');
    }

    
    /**
     * Get the Bond Release transaction of this transaction
     * @return App\Models\User
     */
    public function releaseTransaction()
    {
        return $this->hasOne('App\Models\Transaction', 'id', 'release_id');
    }

    /**
     * Get the Retry transaction of this transaction
     * @return App\Models\User
     */
    public function retryTransaction()
    {
        return $this->hasOne('App\Models\Transaction', 'id', 'retry_id');
    }

    


    /***********************************************************************/
    /*************************  PUBLIC FUNCTIONS  **************************/
    /***********************************************************************/


    /**
     * is this a booking fee payment?
     * @return bool
     */
    public function isBookingFee() {
        return $this->type == self::$TYPE_BOOKING_FEE ? true : false;
    }

    /**
     * is this a bond payment?
     * @return bool
     */
    public function isBond() {
        return $this->type == self::$TYPE_BOND ? true : false;
    }

    /**
     * is this a refund transaction?
     * @return bool
     */
    public function isRefund() {
        return $this->type == self::$TYPE_REFUND ? true : false;
    }

    /**
     * is this a bond release transaction?
     * @return bool
     */
    public function isBondRelease() {
        return $this->type == self::$TYPE_BOND_RELEASE ? true : false;
    }

    /**
     * is payment successful?
     * @return bool
     */
    public function isSuccessful() {
        return $this->transactionStatus == true ? true : false;
    }


    /**
     * Can this transaction reprocessed?
     * @return bool
     */
    public function canRetry() {
        return $this->transactionStatus == false && !$this->retry_id ? true : false;
    }


    /**
     * Can this transaction refunded?
     * @return bool
     */
    public function canRefund() {
        return $this->isBookingFee() && $this->isSuccessful() && !$this->refund_id ? true : false;
    }


    /**
     * Can this transaction released?
     * @return bool
     */
    public function canRelease() {
        return $this->transactionStatus == true && !$this->release_id ? true : false;
    }



    
    /**
     * Get the TotalAmount
     *
     * @return string
     */
    public function getTotalAmount() {
        return $this->TotalAmount * 0.01;
    }


    /**
     * Get the transaction number (ID)
     *
     * @return string
     */
    public function getNumber()
    {
        return sprintf('TR-%04d', $this->id);
    }

    public static function staticGetNumber($id)
    {
        return sprintf('TR-%04d', $id);
    }


    /**
     * Get the transaction status
     *
     * @return string
     */
    public function getStatus()
    {
        switch($this->transactionStatus) {

            case 1:
                return '<span class="label l-green m-0">Approved</span>';
                break;

            case 0:
                return '<span class="label l-red m-0">Failed</span>';
                break;
        }
    }


    /**
     * Get the transaction status
     *
     * @return string
     */
    public function getResponse()
    {
        return json_decode($this->response);
    }



    /**
     * Get the type of the transaction
     */
    public function getType() {

        switch($this->type) {

            case self::$TYPE_BOOKING_FEE: 
                return 'Booking Fee';
                break;

            case self::$TYPE_BOND: 
                return 'Bond Holding';
                break;

            case self::$TYPE_REFUND: 
                return 'Refund';
                break;

            case self::$TYPE_BOND_RELEASE: 
                return 'Bond Release';
                break;

            case self::$TYPE_BOND_CAPTURE: 
                return 'Bond Capture';
                break;

            case self::$TYPE_DIRECT: 
                return 'Direct payment';
                break;
        }

    }



    /**
     * Get Response message
     */
    public function getResponseMessage() {

    }




}
