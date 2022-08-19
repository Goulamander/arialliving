<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Requests\GetRepeatNextDateRequest;

use App\Models\User;
use App\Models\Building;
use App\Models\BookableItem;
use App\Models\Comment;

use App\Models\RecurringEvent;

class RecurringEventController extends Controller
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
     * Get the next repeating date
     * 
     * @param  \Illuminate\Http\Get\GetRepeatNextDateRequest $request
     * 
     * @return Response Next repeating date
     */
    public function getRepeatNextDate(GetRepeatNextDateRequest $request) {
  
        return response()->json([
            'error' => '',
            'data' => RecurringEvent::nextRepeatDate((object) $request->all())
        ], 200);

    }
}
