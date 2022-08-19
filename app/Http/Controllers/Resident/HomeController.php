<?php

namespace App\Http\Controllers\Resident;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseClasses\Controller;

use App\Models\BookableItem;
use Session;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if ($request->has('search')) {
            // return view('resident._items');
        }
        return view('resident.index');
    }

    /**
     * search
     *
     */
    public function search(Request $request)
    {
        return view('resident.search');
    }

    /**
     * Go back to Admin
     */
    public function backToAdmin() {
        
        $to = Session::get('come_from') ? Session::get('come_from') : '/admin';
        
        Session::pull('come_from');
        return redirect($to);
    }


    /**
     * Switch building preview for admin users
     */
    public function switchBuilding($building_id) {

        if(!$building_id) {
            abort(404);
        }

        Session::put('building_preview_id', $building_id);
        return redirect('/');
    }


    /**
     * Preview a building
     */
    public function previewBuilding($building_id) {

        if(!$building_id) {
            abort(404);
        }

        // put the previewed buildings into session 
        Session::put('building_preview_id', $building_id);
        Session::put('come_from', url()->previous());

        return redirect('/');
    }


    /**
     * Preview a building
     */
    public function previewItem($type, $item_id) {

        if(!$item_id) {
            abort(404);
        }

        $item = BookableItem::where('id', $item_id)->first('building_id');

        if(!$item) {
            abort(404);
        }

        // put the previewed buildings into session
        Session::put('building_preview_id', $item->building_id);
        Session::put('come_from', url()->previous());

        return redirect()->route('resident.item.show', [$type, $item_id]);
    }

}
