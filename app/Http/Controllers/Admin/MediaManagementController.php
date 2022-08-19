<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

class MediaManagementController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin|building-manager|admin');
    }

    /**
     * Display the media management
     * use laravel-file-manager package
     * https://github.com/alexusmai/laravel-file-manager
     * @return Response
     */
    public function index($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }

}
