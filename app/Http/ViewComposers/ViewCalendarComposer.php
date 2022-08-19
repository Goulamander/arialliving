<?php

namespace App\Http\ViewComposers;


use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Building;
use App\Models\Category;
use App\Models\BookableItem;

use DB;
use Auth;

class ViewCalendarComposer
{
    

    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        // Can see all buildings
        if( Auth::user()->hasRole(['super-admin', 'admin']) ) {
            $buildings = Building::get();
        }
        // Own only
        else {
            $buildings = Auth::user()->buildings()->get(['id', 'name']);
        }

        // get the items of the first building
        if($buildings) {
            

            $items = BookableItem::select(DB::raw("
                    bookable_items.id,
                    category_id,
                    title,
                    categories.name as category_name,
                    is_thumb,
                    bookable_items.order
                "))
                ->leftJoin('categories', 'categories.id', '=', 'bookable_items.category_id')
                ->where('bookable_items.status', 1)
                ->orderBy('categories.order', 'ASC')
                ->orderBy('bookable_items.order', 'ASC')
                ->get(['id', 'category_id', 'title']);

                $_items = [];

                foreach($items->groupBy('category_id') as $key => $items) {
                    $_items[] = [
                        'text' => $items[0]->category_name,
                        'children' => $items->toArray()
                    ];
                }
                $_items = $_items;
        
        }

        $categories = Category::orderBy('order', 'ASC')->get(['id', 'name', 'color']);

        $view->with(compact('buildings', 'categories', '_items'));
    }

}
