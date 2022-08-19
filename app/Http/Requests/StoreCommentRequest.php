<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'data_type' => 'required|in:building,booking,item,resident',
            'data_id'   => 'required|integer',
            'type'      => 'required|integer',
            'comment'   => 'required',
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
            'data_type.required'  => 'Comment Owner type is required',
            'data_type.in' => 'Invalid Comment Owner type',
            'data_id.required'  => 'Comment Owner ID is required',
            'type.required'  => 'Comment type is required',
            'type.integer' => 'Invalid Comment type',
        ];
    }


}
