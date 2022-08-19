<?php

namespace App\Http\Requests\Resident;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\BookableItem;

use Auth;

class StoreBookingRequest extends FormRequest
{
    

    /**
     * The id of the enquiry being rejected
     *
     * @var string
     */
    protected $item_id;


    /**
     * Instantiate a new instance of the form request
     *
     * @return void
     */
    public function __construct(Request $request) {
        $this->item_id = $request->item_id;
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        // Get the bookable item
        $item = BookableItem::find($this->item_id);

        // Cannot find item
        if(!$item) {
            return response()->json([
                'error' => 'We cannot find the item that you\'re trying to make the booking for.'
            ], 400);
        }

        // General rules
        $rules = [];

        // Room / Hire extra validation
        if(in_array($item->type, [BookableItem::$TYPE_ROOM, BookableItem::$TYPE_HIRE]) ) {
            
            $__rules = [
                'date_start' => 'required|date',
                'date_end'   => 'required|date',
                'time_start' => 'required|date_format:H:i:s',
                'time_end'   => 'required|date_format:H:i:s',
            ];
            $rules = array_merge($__rules, $rules);
        }

        // todo:
        // if($item->type == BookableItem::$TYPE_HIRE) {
        //     $__rules = [
        //         'qty' => 'required|integer'
        //     ];
        //     $rules = array_merge($__rules, $rules);
        // }


        // Card Validation (booking is not free and the resident has no card stored yet)
        if( $item->is_free == false && !Auth::user()->isResidentVip() && !Auth::user()->tokenCustomerID && !$item->isFreeAsAdmin() && $item->isPaymentToAria()) {
     
            $__rules = [
                'card_name' => 'required',
                'card_number' => 'required',
                'card_expiry_month' => 'required',
                'card_expiry_year' => 'required',
                'card_cvn' => 'required'
            ];
            $rules = array_merge($__rules, $rules);
            
        }

        
        // Terms must be accepted, hey
        if($item->getPDFTerms()) {

            $__rules = [
                'accepted_terms' => 'required'
            ];
            $rules = array_merge($__rules, $rules);
        }
        

        // Sign it, please
        if($item->is_signature_required) {

            $__rules = [
                'signature' => 'required'
            ];
            $rules = array_merge($__rules, $rules);
        }

        return $rules;
    }



    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // 
            'date_start.required'     => 'Select date for your booking',
            'date_end.required'       => 'Select date for your booking',
            'time_start.required'     => 'Select time for your booking',
            'time_end.required'       => 'Select time for your booking',
            //
            'card_name.required'  => 'Card Name is required',
            'card_number.required'  => 'Card Number is required',
            'card_expiry_month.required'  => 'Card Expiry Year is required',
            'card_expiry_year.required'  => 'Card Expiry Month is required',
            'card_cvn.required'  => 'Card CVN is required',
            //
            'accepted_terms.required'   => 'Accepting Terms is required',
            'signature.required'         => 'Signature is required'
        ];
    }
}
