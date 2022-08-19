<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class RoleController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }


    /**
     * Display the User List
     *
     * @return Response
     */
    public function index() {
        return view( Route::currentRouteName() );
    }


    /**
     * User single page view
     *
     * @return Response
     */
    public function show() {
        return view(Route::currentRouteName());
    }
    


}



