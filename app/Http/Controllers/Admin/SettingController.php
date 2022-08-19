<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Models\Setting;
use App\Models\Category;
use DataTables;
use DB;

class SettingController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        return view('app.settings.index');
    }



    /**
     * Get a category by id
     * 
     * @param  int $category_id
     * @return Response/Json
     */
    public function getCategory($category_id) {

        $category = Category::where('id', $category_id)->first();

        if(!$category) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Category']),
                'data' => []
            ], 400);
        }

        $category->update_route = route('app.settings.category.store', $category->id);

        return response()->json([
            'error' => '',
            'data' => $category
        ], 200);
    }



    /**
     * Create/Update a category
     *
     * @param  Request $request
     * @param  int $category_id || 0
     * @return Response/Json
     */
    public function storeCategory(Request $request, $category_id = 0) {

        $category = Category::updateOrCreate(['id' => $category_id], $request->all());

        if( ! $category ) {
            return response()->json([
                'error' => $category_id == 0 ? __('messages.cannotSave', ['type' => 'Category']) : __('messages.notFound', ['type' => 'Category']),
                'data' => []
            ], 400);
        }

        return response()->json([
            'error' => '',
            'message' => $category_id == 0 ? __('messages.created', ['type' => 'Category']) : __('messages.updated', ['type' => 'Category']),
            'data' => [
                'id' => $category_id
            ]
        ], 200);
    }



    /**
     * Store the order of categories
     * 
     * @param  Request $request
     * @return Response/Json
     */
    public function storeCategoryOrder(Request $request) {

        if(! $request->order ) {
            return response()->json([
                'error' => 'Invalid order',
                'data' => []
            ], 400);
        }

        foreach($request->order as $order => $id) {
            Category::where('id', $id)->update(['order' => $order]);
        }
        
        //
        return response()->json([
            'error' => '',
            'message' => __('messages.updated', ['type' => 'Category Order']),
            'data' => []
        ], 200);

    
    }



    /**
     * Delete a Category
     * 
     * @param  Request $request
     * @param  int $category_id || 0 
     * @return Response/Json
     */
    public function deleteCategory(Request $request, $category_id) {

        $category = Category::where('id', $category_id)->with('items:id,category_id')->first();

        if(!$category) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Category']),
                'data' => []
            ], 400);
        }

        // Check if category has any items
        if( ! $category->items->isEmpty() ) {
            return response()->json([
                'error' => 'This category has active bookable items, it cannot be deleted.',
                'data' => []
            ], 400);
        }

        $category->forceDelete();

        //
        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'Category']),
            'data' => [
                'id' => $category_id
            ]
        ], 200);
    }



    /**
     * Update Settings (Email templates)
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {

        // Convert back key names to original, add back the dots.
        $data = [];

        foreach($request->all() as $k => $v) {
            $valid_key = str_replace('__', '.', $k);
            $data[$valid_key] = $v;
        }

        // Get the settings for validating changes
        $Settings = Setting::get(['id', 'code', 'value']);

        // Update changes
        foreach ($data as $key => $val) {
            Setting::where('code', $key)->update(['value' => $val]);
        }

        return response()->json([
            'error' => '',
            'message' => __('messages.updated', ['type' => 'Email template']),
            'data' => [],
        ], 200);
    }



    /**
     * List Categories and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function List(Request $request)
    {
        $items = Category::select(DB::raw("
            categories.*,
            (SELECT
                COUNT(bookable_items.id)
                FROM bookable_items
                WHERE bookable_items.category_id = categories.id
            ) AS items_no
        "))->orderBy('order', 'ASC')->get();



        $JSON = DataTables::of($items)
            // Name
            ->addColumn('name', function(Category $category) {
                return '
                    <span class="row-col title open-data drag-handle" data-open-category="'.$category->id.'">
                        <i class="material-icons">drag_handle</i>
                        <span>'.$category->name.'</span>
                    </span>';
            })
            // Color
            ->addColumn('color', function(Category $category) {
                return '<span class="initials _bg" style="background-color: '.$category->color.'"></span>';
            })
            // No. of items
            ->addColumn('items_no', function(Category $category) {
                return $category->items_no > 0 ? $category->items_no.' '.str_plural('item', $category->items_no) : '-';
            })
            // Status
            ->addColumn('status', function(Category $category) {
                return $category->statusLabel();
            })
            // Actions
            ->addColumn('actions', function(Category $category) {
                return '
                <div class="btn-hspace">
                    <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                        Actions <i class="material-icons">expand_more</i>
                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.settings.category.delete', $category->id).'">Delete</a></li>
                    </ul>
                </div>';
            })
            // Decode HTML chars
            ->rawColumns([
                'name', 
                'color', 
                'items_no', 
                'status', 
                'actions', 
            ])
            // Set Row Attributes
            ->setRowAttr([
                'data-id' => function(Category $category) {
                    return $category->id;
                }
            ]);

            // return for ajax
            return $JSON->toJson();
    }

}
