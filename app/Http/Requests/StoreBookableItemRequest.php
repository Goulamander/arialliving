<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

use App\Models\BookableItem\BookableItemEvent;

class StoreBookableItemRequest extends FormRequest
{


    /**
     * The id of the enquiry being rejected
     *
     * @var string
     */
    protected $type;
    protected $event_type;

    /**
     * Instantiate a new instance of the form request
     *
     * @return void
     */
    public function __construct(Request $request) {
        $this->type = $request->type;
        $this->event_type = $request->event_type;
        $this->all_day = $request->all_day;
        $this->recurring_all_day = $request->recurring_all_day;
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

        // General
        $rules = [
            "title"        => "required|max:150",
            "status"       => "nullable|integer|in:0,1,2,3",
            "building_id"  => "required|integer"
        ];

        // Event
        if($this->type == 'event') {

            $event_rules = [
                "location_name"  => "string|required",
                "location_id"    => "nullable|integer",
                "event_type"     => "integer|in:1,2",
                "event_date"     => "required|date_format:Y-m-d",
                "event_from"     => "nullable",
                "event_to"       => "nullable",
                "attendees_limit" => "integer|nullable",
                "allow_guests"   => "integer|nullable",
            ];

            $event_recurring_rules = [
                "repeat_next"            => "nullable|date_format:Y-m-d",
                "repeat_start"           => "required",
                "repeat_end"             => "nullable|date_format:Y-m-d",
                "repeat_every"           => "required|integer",
                "frequency"              => "required",
                "frequency_week_days"    => "",
                // overwrite some single event validations
                "event_date"             => "nullable",
            ];

            $rules = array_merge($rules, $event_rules);

            // Recurring
            if($this->event_type && ($this->event_type == BookableItemEvent::$TYPE_REPEATING)) {
                $rules = array_merge($rules, $event_recurring_rules);
            }

            
            if($this->event_type) {
                // If event not all day make from to fields required
                if($this->all_day == false && $this->event_type == BookableItemEvent::$TYPE_SINGLE) {
                    $rules["event_from"] = "required";
                    $rules["event_to"] = "required";
                }
                if($this->recurring_all_day == false && $this->event_type == BookableItemEvent::$TYPE_REPEATING) {
                    $rules["recurring_event_from"] = "required";
                    $rules["recurring_event_from"] = "required";
                }
            }

      
        }

        // Room
        if($this->type == 'room') {

            $room_rules = [
                // Fee and visibility
                'is_private' => 'required|in:0,1',
                'admin_fee'  => 'nullable|numeric',
                // Booking options
                'daily_booking_limit' => 'nullable|integer',
                'booking_from_time'   => 'required',
                'booking_to_time'     => 'required',
                'booking_max_length'  => 'required|integer',
                'booking_min_length'  => 'required|integer',
                'allow_multiday'      => 'required|integer|in:0,1',
                'booking_gap'         => 'required|integer',
                // Booking policy
                'prior_to_book_hours'  => 'required|integer',
                'cancellation_cut_off' => 'required|integer',

                'clearing_fee' => 'nullable'
            ];
            $rules = array_merge($rules, $room_rules);
        }

        // Hire
        if($this->type == 'hire') {

            $hire_rules = [
                // 'available_qty' => '',
                // 'item_price'    => '',
                // 'bond_amount'   => '',
                // 'office_hours'  => '',
            ];

            $rules = array_merge($rules, $hire_rules);

        }

        // Service
        if($this->type == 'service') {

            $service_rules = [
                // 'line_items'    => '',
                // 'office_hours'  => '',
            ];

            $rules = array_merge($rules, $service_rules);

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
            'title.required'  => 'The title field is required',
            // 'event_from.required' => 'The event from field is required',
            // 'event_to.required' => 'The event to field is required',
            // 'recurring_event_from.required' => 'The event from field is required',
            // 'recurring_event_to.required'   => 'The event to field is required',
        ];
    }
}
