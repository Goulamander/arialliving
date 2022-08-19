<?php

namespace App\Http\Requests\BaseClasses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ServiceRequest extends FormRequest
{

    /**
     * The id of the service being modified
     * @var int
     */
    protected $service_id;

    /**
     *
     *
     */
    public function __construct(Request $request)
    {
        $this->service_id = $request->service_id;
    }

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
            'name'              => 'required|max:255|unique:services,name,'.$this->service_id,
            'descripiton'       => 'sometimes|max:255',
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
            'name.required'   => 'Please provide a name for the service',
            'name.unique'     => 'This service name has already been used',
            'name.max'        => 'The service name must be less than 255 characters',
            'description.max' => 'The service description must be less than 255 characters',
        ];
    }
}
