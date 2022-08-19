<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarketingCommunicationsRequest extends FormRequest
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
        $id = "";

        if( $this->route()->parameters() ) {
            $id = $this->route()->parameters()['id'];
        }

        return [
            'subject'  => 'required|max:255',
            'body'   => 'required',
            'send_via'     => 'required',
            'receiver'     => 'required',
            'status'     => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }
}
