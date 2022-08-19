<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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

        return [
            'first_name'  => 'required|max:255',
            'last_name'   => 'required|max:255',
            'email'       => 'required|max:255|unique:users,email,'.$user_id,
            'role_id'     => 'required|numeric|in:1,2,3,4,5',
            'mobile'      => 'nullable',
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
            'email.unique' => 'This email is already taken. Please choose another one.',
            'role_id.numeric' => 'Invalid Role, please select role from the available options.' ,
            'role_id.in' => 'Invalid Role, please select role from the available options.'
        ];
    }
}
