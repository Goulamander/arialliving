<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
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
            'name'              => 'required|max:255',
            'street_address_1'  => 'required|max:255',
            'street_address_2'  => 'max:255',
            'suburb'            => 'required|max:255',
            'state'             => 'required|max:255',
            'postcode'          => 'required|digits:4|integer',
            'phone'             => 'max:255'
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
            'name.required'   => 'The name field is required.',
            'postcode.digits'    => 'Postcode should be 4 digits.',
            'postcode.numeric' => 'Postcode should be numbers only.',
        ];
    }
}
