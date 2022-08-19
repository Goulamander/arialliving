<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetRepeatNextDateRequest extends FormRequest
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
            'repeat_start'  => 'date|required',
            'repeat_end'    => 'date|nullable',
            'repeat_every'  => 'required|integer',
            'frequency'     => 'required|in:7,30,365',
            'frequency_week_days' => 'required_if:frequency,7'
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
            'repeat_start.required' => 'Repeat Start is required',
            'repeat_start.date'     => 'Repeat Start is invalid',
            'repeat_end.date'       => 'Repeat End is invalid',
            'repeat_every.required' => 'Repeat Every is required',
            'repeat_every.integer'  => 'Frequency is invalid',
            'frequency.in'          => 'Frequency is invalid',
            'frequency_week_days.required_if' => 'One or multiple days must be specified for weekly recurring',
        ];
    }
}
