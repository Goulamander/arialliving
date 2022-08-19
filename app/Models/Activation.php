<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Activation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_activations';


    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'token'
    ];


	/***********************************************************************/
	/************************* ELOQUENT RELATIONSHIPS **********************/
    /***********************************************************************/
    
    /**
     * Get the user associated with this Activation.
     * @return App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }



	/***********************************************************************/
	/*************************  HELPER FUNCTIONS  **************************/
    /***********************************************************************/
    

    /**
     * Assign an Activation object to the user if they don't have one.
     * Otherwise, reset the token in the Activation object.
     *
     * @param \App\Models\User $user
     * @return \App\Models\Activation
     */
     public function createActivation(User $user) {
         // Check if the user already has an Activation
         if (!$user->activation) {
             // Assign user to the activation object
             $this->setActivation($user);
             // Save the Activation object to the database
             $this->save();
             return $this;
         } else {
             $this->resetActivation($user);
             return $this;
         }
     }


    /**
     * Sets a random token as the Activation token for a specified user
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function setActivation(User $user) {
        // Set the attributes of the object
        $this->user_id = $user->id;
        $this->token = $this->generateToken();
        // Save to database
        $this->save();
    }


    /**
     * Regenerate a token for a user who already has a token
     * stored in the database.
     *
     * @return void
     */
    public function resetActivation(User $user) {
        // Find the activation object for the user
        $activation = $this->activationByUser($user)->first();
        // Generate a random token
        $token = $this->generateToken();
        // Set the new token attribute of the object
        $this->token = $token;
        // Update the token in the database
        $activation->update(['token' => $token]);
    }


    /**
     * Generate a random token.
     *
     * @return string randomly generated token
     */
    public function generateToken() {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }


    /**
     * Get the User Activation object by a specified user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return \App\Models\Activation
     */
    public function scopeActivationByUser($query, $user) {
        return $query->where('user_id', '=', $user->id);
    }


    /**
     * Get the User Activation object by a specified token
     *
     * @return Activation the Activation object
     */
    public function scopeActivationByToken($query, $token) {
        return $query->where('token', $token);
    }


    /**
     * Delete the Activation object.
     *
     * @return void
     */
    public function deleteActivation() {
        Activation::where('token', $this->token)->delete();
    }


  }
