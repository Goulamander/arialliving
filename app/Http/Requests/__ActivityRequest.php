<?php

namespace App\Http\Requests\BaseClasses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Models\Job;

class ActivityRequest extends FormRequest
{
    /**
     *
     *
     */
    protected $job_id;

    /**
     *
     */
    protected $assigned_users;

    /**
     *
     *
     */
    public function __construct(Request $request)
    {
        $this->job_id = $request->job_id;
        $this->assigned_users = $request->input('assigned_users', []);
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
            'title'                 => 'required|max:255',
            'description'           => 'required|max:255',
            'date'                  => 'required|date',
            'assigned_users'        => 'array',
            'is_complete'           => 'boolean',
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
            if (!$this->numeric_array() && !$this->usersAssignedToJob()) {
                $validator->errors()
                          ->add('fail', 'Please select users from the list');
            }
        });
    }

    /**
     * Check if the users selected exist in the database
     *
     * @return boolean
     */
    private function usersAssignedToJob()
    {
        // Get all the users
        $assigned_users = $this->assigned_users;
        // Get the job with the assigned users.
        $job = Job::find($this->job_id)
                  ->with(['users' => function($query) use ($assigned_users) {
                      $query->whereIn('users.id', $assigned_users);
                  }])->first();
        // Check if inputted users are assigned to the job
        foreach($job->users as $user) {
            if (!in_array($user->id, $assigned_users)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the users selected are IDs
     *
     * @return boolean
     */
    private function numeric_array()
    {
        foreach($this->assigned_users as $user_id) {
            if(!is_int($user_id)) {
                return false;
            }
            return true;
        }
    }
}
