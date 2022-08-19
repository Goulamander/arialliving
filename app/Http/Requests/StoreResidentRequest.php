<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Http\Request;

class StoreResidentRequest extends FormRequest
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
        // grab user_id for the email|unique validation
        $user_id = "";
        
        if( $this->route()->parameters() ) {
            $user_id = $this->route()->parameters()['user_id'];
        }

        $min_role_id = User::$ROLE_RESIDENT;

        return [
            'first_name'     => 'required|max:255',
            'last_name'      => 'required|max:255',
            'email'          => 'required|max:255|unique:users,email,'.$user_id,
            'mobile'         => 'nullable',
            'building_id'    => 'required|integer',
            'role_id'        => 'required|integer|min:'.$min_role_id,
            'unit_no'        => 'nullable',
            'unit_type'      => 'nullable|in:1,2,3,4,5',
            'relation_start' => 'date_format:Y-m-d|nullable',
            'relation_end'   => 'date_format:Y-m-d|nullable'
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
            'email.unique'   => 'This email is already taken. Please choose another one.',
            'building_id.required' => 'Please select a Building',
            'role_id.required' => 'Resident Level is required',
            'role_id.min' => 'Invalid Resident level',
            'relation_start.date_format'  => 'Incorrect Residency Start date provided',
            'relation_end.date_format'    => 'Incorrect Residency End date provided',
        ];
    }

}
