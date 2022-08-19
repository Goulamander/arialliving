<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
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
        return [
            'user_id'           => 'required|integer',
            'building_id'       => 'required|integer',
            'bookable_item_id'  => 'required|integer',
            'type'              => 'required|in:1,2,3,4|integer',
            'start'             => 'required|date',
            'end'               => 'required|date',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required'          => 'User field is required.',
            'building_id.required'      => 'Building field is required',
            'bookable_item.required'    => 'Invalid booking',
            'type.in'                   => 'Invalid type for a booking',
            'type.numeric'              => 'Invalid type for a booking',
        ];
    }
}
