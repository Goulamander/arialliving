<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\User;

class UserRegisterRequest extends FormRequest
{
    /**
     * The email being used to sign up
     *
     * @var string
     */
    protected $email;

    /**
     * Instantiate a new instance of the form request
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->email = $request->input('email');
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
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
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
            'name.required'     => 'A name is required to sign up',
            'name.max'          => 'Your name should not be more than 255 characters',
            'email.unique'      => 'This email is registered',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->accountActive()) {
                $validator->errors()
                          ->add('email', 'This email is already registered in our system.');
            }
        });
    }

    /**
     * Check if the email being used to sign up is already active
     *
     * @return boolean
     */
    private function accountActive()
    {
        // Attempt to find the user by email
        $user = User::where('email', $this->email)
                    ->where('activated', 1)
                    ->first();
        return $user ?? false;
    }
}
