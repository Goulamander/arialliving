<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\UserSetting;
use Auth;
use Carbon\Carbon;


class ProfileController extends Controller
{

    /**
     * Instantiate a new controller instance
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');        
    }


    /**
     * Show the resident profile
     *
     * @return Response
     */
    public function show() {
        return view(Route::currentRouteName());
    }


    /**
     * Store the resident contact details
     * 
     * @param $request
     * @return $response
     * 
     */
    public function storeContact(Request $request) {

        // _validate email address
        if($request->email) {

            $is_taken = User::where('email', $request->email)
                    ->where('id', '!=', Auth::id())
                    ->first();

            // address is taken
            if($is_taken) {
                return Redirect::to('/admin/profile')->with('error', 'This email address is already used by another user.');
            }
        }

        // Save the changes
        Auth::user()->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'  => $request->email,
            'mobile' => $request->mobile,
        ]);

        return response()->json([
            'error' => '',
            'message' => 'Saved',
            'data' => []
        ], 200);
        
    }



    /**
     * Store the resident contact details
     * 
     * @param $request
     * @return $response
     * 
     */
    public function storeNotiSetting(Request $request) {

        $update_data = [];
        if(isset($request->additional_password_prompt)){
            $update_data['additional_password_prompt'] = !$request->additional_password_prompt ? UserSetting::$ADDITIONAL_PASSWORD_PROMPT_OFF : UserSetting::$ADDITIONAL_PASSWORD_PROMPT_ON;
        }
        if(isset($request->email_notification)){ 
            $update_data['notifications_email'] = gettype($request->email_notification) == 'array' ? implode(',',$request->email_notification) : $request->email_notification;
        }
        if(isset($request->notifications_sms)){
            $update_data['notifications_sms'] = gettype($request->notifications_sms) == 'array' ? implode(',',$request->notifications_sms) : $request->notifications_sms;
        }
        // update data
        Auth::user()->settings()->updateOrCreate([], $update_data);

        return response()->json([
            'error' => '',
            'message' => 'Saved',
            'data' => []
        ], 200);
    }


    /**
     * Store the resident credit card details
     * 
     * @param $request
     * @return $response
     * 
     */
    public function storeCreditCard(Request $request) {

        // todo: build validation

        $user = Auth::user();

        $store_card_response = $user->storeCreditCard($request->all());

        if($store_card_response['status'] === false) {
            return response()->json([
                'error' => $store_card_response['errors'],
                'data' => []
            ], 400);
        }

        return response()->json([
            'error' => '',
            'data' => []
        ], 200);
    }


    /**
     * Store the resident new password
     * 
     * @param $request
     * @return $response
     */
    public function storePassword(Request $request) {

        $request->validate([
            'password' => ['required', new MatchOldPassword],
            'new_password' => ['required', 'min:8'],
            'new_password_conf' => ['same:new_password'],
        ]);

        Auth::user()->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'error' => '',
            'data' => [],
            'message' => __('messages.updated', ['type' => 'Password'])
        ], 200);
    }










}
