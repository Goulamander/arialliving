<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\BaseClasses\Controller;

use App\Models\FilePond;

use App\Models\BookableItem;
use App\Models\Building;
use App\Models\RetailStore;

use App\Traits\FileManager;

use Storage;
use Image;

class FilePondController extends Controller
{

    /**
     * @var FilePond
     */
    private $filepond;


    public function __construct(FilePond $filepond) {
        $this->filepond = $filepond;
    }


    /**
     * Uploads the file to the temporary directory
     * and returns an encrypted path to the file
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');

        if(!$file) {
            Response::make('No files found', 400);
        }

        $tempPath = realpath(sys_get_temp_dir());

        $filePath = tempnam($tempPath, $file->getClientOriginalName());
        $filePath .= '.' . $file->extension();

        $name = $file->getClientOriginalName();
        $extension = $file->extension();

        $filePathParts = pathinfo($filePath);

        if (!$file->move($filePathParts['dirname'], $filePathParts['basename'])) {
            return Response::make('Could not save file', 500);
        }

        /** Process: Upload | Resize | Create thumb sizes | Update database */
        $data = [
            'name' => $name,
            'extension' => $extension,  
            'temp_path' => $filePath,
            'location'  => $request->folder
        ];

        if( !$request->process_type ) {
            return Response::make('Could not save file', 500);
        }

        // process_type
        switch($request->process_type) {

            case 'thumbnail':
                return FileManager::uploadThumbnails((object) $data);
                break;

            case 'gallery-image':
                return FileManager::uploadImageToGallery((object) $data);
                break;

            case 'pdf-attachment':
                return FileManager::uploadPDFTerm((object) $data);
                break;

            default: 
                return Response::make('Could not save file', 500);
                break;
        }
        return Response::make('', 200);
    }



    /**
     * Takes the given encrypted filepath and deletes
     * it if it hasn't been tampered with
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {

        if( !$request->path ) {
            return response()->json([
                'error'  => 'Image not found',
                'message' => ''
            ], 400);
        }

        // Remove all sizes
        Storage::disk('public')->delete($request->path.'_820x500.jpg');
        Storage::disk('public')->delete($request->path.'_180x180.jpg'); 

        // Update the database records
        $a = array_filter(explode('/', $request->folder));

        $item_id = array_pop($a);
        $item_type = array_shift($a);

        switch($item_type) {

            case 'items':
                BookableItem::where('id', $item_id)->update(['is_thumb' => NULL]);
                break;

            case 'buildings':
                Building::where('id', $item_id)->update(['is_thumb' => NULL]);
                break;

            case 'stores':
                RetailStore::where('id', $item_id)->update(['thumb' => NULL]);
                break;
        }

        return response()->json([
            'error'  => '',
            'message' => 'Image removed'
        ], 200);
    }


    /**
     * Get a file (not working on Server !!!)
     */
    public function get($file_name) {

        $file_path = decrypt($file_name);
        
        if(!$file_path) {
            // todo: build response
        }

        return Image::make(public_path($file_path))->response();
    }



}
