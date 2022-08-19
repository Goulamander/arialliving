<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


use App\Models\User;
use App\Models\Cart;
use App\Models\LineItem;

use DB;
use Auth;

use Illuminate\Support\Arr;

use Carbon\Carbon;


class CartController extends Controller
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
     * Sync the cart
     *
     * @param $Request
     * 
     * @param $item_type - BookableItem type
     * @param $item_id   - BookableItem ID
     * 
     * @return Response
     */

    public function sync(Request $request, $item_type, $item_id)
    {

        $user_id = Auth::id();

        $cart = Cart::where([
            'user_id' => $user_id,
            'item_id' => $item_id,
        ])->first();

        $cart_items = [];
        if($cart) {
            $cart_items = json_decode($cart->items, true);
        }

        switch($request->action) {

            // Update QTY
            case 'update':
                if($cart_items) {
                    foreach($cart_items as $key => $item) {
                        if($item['id'] == $request->item_id) {
                            $cart_items[$key]['qty'] = $request->qty;
                            break;
                        }
                    }
                }
                $cart->update([
                    'items' => json_encode($cart_items)
                ]);
                break;

            // Remove an item
            case 'remove':
                if($cart_items) {
                    foreach($cart_items as $key => $item) {
                        if($item['id'] == $request->item_id) {
                            unset($cart_items[$key]);
                            break;
                        }
                    }
                }
                $cart->update([
                    'items' => json_encode($cart_items)
                ]);
                break;

            // Add an item
            case 'add':

                if($cart) {

                    $is_in_cart = false;

                    // update the QTY only if the item is in Cart already.
                    foreach($cart_items as $key => $item) {
                        if($item['id'] == $request->item_id) {
                            $cart_items[$key]['qty'] = $item['qty'] + $request->qty;
                            $is_in_cart = true;
                            break;
                        }
                    }

                    // Add the item to the cart if not found.
                    if($is_in_cart == false) {

                        array_push($cart_items, [
                                'id' => $request->item_id, 
                                'qty' => $request->qty
                            ]
                        );
                    }

                    $cart->update([
                        'items' => json_encode($cart_items)
                    ]);
                }
                // no cart set yet
                else {
 
                    $cart_items = [];

                    array_push($cart_items, [
                        'id'  => $request->item_id, 
                        'qty' => $request->qty
                    ]);
          
                    Cart::create([
                        'user_id' => $user_id,
                        'item_id' => $item_id,
                        'items' => json_encode($cart_items)
                    ]);
                }
                break;
        }

        $data = [];

        // Return the Line Item (for action: add)
        if($request->action == 'add') {
            // Get the line-item's price
            $line_item = LineItem::where('id', $request->item_id)->first();

            if( $line_item ) {
                if(!!Auth::user()->isResidentVip()){
                    $line_item->price = 0; 
                }
                $data = compact('line_item');
            }
        }

        //
        return response()->json([
            'error' => '',
            'data' => $data
        ], 200);
    }


}
