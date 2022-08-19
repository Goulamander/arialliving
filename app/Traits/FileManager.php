<?php

namespace App\Traits;
 
use Illuminate\Support\Facades\Response;

use App\Models\Building;
use App\Models\BookableItem;
use App\Models\RetailStore;

use Storage;
use Image;

trait FileManager {
 
    public static $thumb_sizes = [
        '820x500',
        '180x180',
    ];

    /**
     * Upload thumbnail when submitting form
     *  - used at: deals/line items
     */
    public function uploadInlineThumbnail($file, $location) {

        $file = (object) json_decode($file);

        // _validate file type
        if( ! in_array($file->type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']) ) {
            return false;
        }

        // _no location provided
        if( ! $location ) {
            return false;
        }

        $file_name = 'thumb_'. str_replace('.', '', microtime(true));

        // create sizes
        foreach(self::$thumb_sizes as $size) {

            $s = explode('x', $size);
            
            $img = (string) Image::make($file->data)->fit($s[0], $s[1])->encode('jpg', 95);
            
            Storage::disk('public')->put($location.'/'.$file_name.'_'.$size.'.jpg', $img);
        }

        return $file_name;
    }


    /**
     * Remove thumbnails when updating form
     *  - used at: deals/line items
     */
    public function removeInlineThumbnail($path, $thumb_name) {

        foreach(self::$thumb_sizes as $size) {
            Storage::disk('public')->delete($path.$thumb_name.'_'.$size.'.jpg');
        }

        return true;
    }



    /**
     * Upload thumbnail for an item or building
     *   - create two sizes
     */
    public static function uploadThumbnails($file) {

        // _validate extension
        if( ! in_array($file->extension, ['jpeg', 'jpg', 'JPG', 'png', 'gif']) ) {
            return response()->json([
                'error' => 'Image file (.jpg, .png or .gif)',
                'data' => []
            ], 400);
        }
    
        // _check image location
        if( ! $file->location ) {
            return response()->json([
                'error' => 'File locations is missing',
                'data' => []
            ], 400);
        }

        // Actual size (820x500)
        $image = (string) Image::make($file->temp_path)
            ->resize(820, 500, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode('jpg', 95);
       
        // Thumb size (180x180)
        $thumb = (string) Image::make($file->temp_path)->fit(180, 180)->encode('jpg', 95);

        $file_name = 'thumb_'. str_replace('.', '', microtime(true));
    
        Storage::disk('public')->put($file->location.'/'.$file_name.'_820x500.jpg', $image);
        Storage::disk('public')->put($file->location.'/'.$file_name.'_180x180.jpg', $thumb); 

        $path_parts = array_diff(explode('/', $file->location), [""]);
        $data_type = $path_parts[1];
        $data_id = $path_parts[2];
   
        switch($data_type) {

            case 'items':
                BookableItem::where('id', $data_id)->withTrashed()->update([
                    'is_thumb' => $file_name
                ]);
                break;

            case 'buildings':
                Building::where('id', $data_id)->withTrashed()->update([
                    'is_thumb' => $file_name
                ]);
                break;

            case 'stores':
                RetailStore::where('id', $data_id)->withTrashed()->update([
                    'thumb' => $file_name
                ]);
                break;
        }

        return response()->json([
            'error' => false,
            'message' => 'Image uploaded',
            'data' => [
                'url' => $file->location.$file_name.'_180x180.jpg',
                'file_name' => $file_name,
            ]
        ], 200);
    }



    /**
     * Upload an image to the Image Gallery (item or building)
     *   - create two sizes
     */
    public static function uploadImageToGallery($file) {
 
         // _validate extension
         if( ! in_array($file->extension, ['jpeg', 'jpg', 'JPG', 'png', 'gif']) ) {
            return response()->json([
                'error' => 'Image file (.jpg, .png or .gif)',
                'data' => []
            ], 400);
        }
        if( ! $file->location ) {
            return response()->json([
                'error' => 'File locations is missing',
                'data' => []
            ], 400);
        }

        // convert image to jpg
        $image = (string) Image::make($file->temp_path)->encode('jpg', 95);
       
        // create the thumb size
        $thumb = (string) Image::make($file->temp_path)
            ->resize(300, 200, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->encode('jpg', 95);
    
        
        // Create the image name
        $number = floor( count(Storage::disk('public')->allFiles($file->location)) / 2 ) + 1;
        // add time to file-name to avoid browser cache after each update
        $file_name = $number.'_'. str_replace('.', '', microtime(true)); 

        Storage::disk('public')->put($file->location.'/'.$file_name.'.jpg', $image);
        Storage::disk('public')->put($file->location.'/'.$file_name.'_thumb.jpg', $thumb);

        return response()->json([
            'error' => false,
            'message' => 'Image uploaded',
            'data' => [
                'url' => $file->location.'/'.$file_name.'_thumb.jpg'
            ]
        ], 200);
    }


    /**
     * Upload a PDF to the terms (item or building)
     *   - create two sizes
     */
    public static function uploadPDFTerm($file) {
        
        // _validate extension
        if( ! in_array($file->extension, ['pdf']) ) {
            return response()->json([
                'error' => 'Only PDF file accepted (.pdf)',
                'data' => []
            ], 400);
        }

        if( ! $file->location ) {
            return response()->json([
                'error' => 'File locations is missing',
                'data' => []
            ], 400);
        }

        $number = floor( count(Storage::disk('public')->allFiles($file->location)) / 2 ) + 1;

        $file_path = $file->location.'/'.$number.'___'.$file->name;
        
        Storage::put($file_path, file_get_contents($file->temp_path));
        
        return response()->json([
            'error' => false,
            'message' => 'PDF uploaded',
            'data' => [
                'url' => $file_path
            ]
        ], 200);
    }
 
}