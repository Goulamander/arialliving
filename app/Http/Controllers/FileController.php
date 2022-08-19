<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\BaseClasses\Controller;

use App\Models\Building;
use App\Models\BookableItem;

use Storage;
use Image;

class FileController extends Controller
{

    /**
     * File Storage
     *
     * @var string
     */
    protected $disk;


    /**
     * Instantiate a new controller instance
     *
     * @return void
     */
    public function __construct() {
        $this->disk = Storage::disk('public');
    }



    /**
     * Remove a file by its thumbnail path
     * 
     * @param Request
     * @return Response/Json
     */
    public function removeGalleryImage(Request $request) {

        if(!$request->thumb_path) {
            return response()->json([
                'error'  => 'File cannot be found.',
            ], 400);
        }

        // Remove thumb
        $this->disk->delete($request->thumb_path);

        // Remove file
        $file_path = str_replace('_thumb', '', $request->thumb_path);
        $this->disk->delete($file_path);

        return response()->json([
            'error'  => false,
            'message' => 'File removed'
        ], 200);
    }



    /**
     * Remove a PDF attachment
     * 
     * @param Request
     * @return Response/Json
     */
    public function removePDFAttachment(Request $request) {

        if(!$request->file_path) {
            return response()->json([
                'error'  => 'File cannot be found.',
            ], 400);
        }

        $this->disk->delete($request->file_path);

        return response()->json([
            'error'  => false,
            'message' => 'File removed'
        ], 200);
    }



    /**
     * Re-Order Gallery Images
     */
    public function orderGalleryImages(Request $request) {

        if(!$request->order) {
            return false;
        }

       $updated = [];

        foreach($request->order as $key => $file_path) {

            $a = explode('/', $file_path);
            $file_name = $a[count($a)-1];

            // File thumb path
            $a[count($a)-1] = preg_replace('/^(.*?)_/', $key.'_', $file_name);
            $new_file_path = '/'.implode('/', $a);
            
            // Add to the updated array
            $updated[$file_path] =  $new_file_path;
            
            $file_path = '/'.$file_path;

            if($file_path === $new_file_path) {
                continue;
            }

            // Rename
            $this->disk->move($file_path, $new_file_path);
            $this->disk->move(str_replace('_thumb', '', $file_path), str_replace('_thumb', '', $new_file_path));
        }

        return response()->json([
            'error'  => false,
            'data' => $updated
        ], 200);
    }


    /**
     * Re-Order PDF Terms
     */
    public function orderPDFTerms(Request $request) {

        if(!$request->order) {
            return false;
        }

        $updated = [];

        foreach($request->order as $key => $file_path) {

            $a = explode('/', $file_path);
            $file_name = $a[count($a)-1];

            // File thumb path
            $a[count($a)-1] = preg_replace('/^(.*?)___/', $key.'___', $file_name);
            $new_file_path = implode('/', $a);
            

            // Add to the updated array
            $updated[$file_path] =  $new_file_path;
            
            $file_path = $file_path;

            if($file_path === $new_file_path) {
                continue;
            }

            // Rename
            $this->disk->move('/'.$file_path, '/'.$new_file_path);
        }

        return response()->json([
            'error'  => false,
            'data' => $updated
        ], 200);
    }




    /**
     * Move a file 
     */
    public function move(Request $request) {

        if(!$request->old_path || $request_new_path) {
            return response()->json([
                'error'  => 'Incorrect or Missing File Name',
            ], 400);
        }

        $this->disk->move($request->old_path, $request_new_path);

        return response()->json([
            'error'  => false,
            'message' => 'File Updated'
        ], 200);
    }


    /**
     * Rename a file 
     * 
     * @param Request $request
     */
    public function rename(Request $request) {

        $a = explode('/', $request->file_path);
        $file_name = $a[count($a)-1];


        // remove the order number from the file, so it won't be replaced in the next line
        $file_name = preg_replace('/^(.*?)___/', '', $file_name);

        $new_file_path = str_replace($file_name, str_replace(' ', '-', $request->new_name).'.pdf', $request->file_path);

        if($new_file_path != $request->file_path) {
            $this->disk->move($request->file_path, $new_file_path);
        }

        return response()->json([
            'error'  => false,
            'message' => 'File Updated',
            'data' => [
                'PDFUpdate' => true,
                'old_path' => $request->file_path,
                'new_path' => $new_file_path
            ]
        ], 200);

    }


}
