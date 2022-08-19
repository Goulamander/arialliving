<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

use App\Exceptions\InvalidPathException;


class FilePond extends Model
{


    /**
     * Converts the given path into a filepond server id
     *
     * @param string $path
     * @return string
     */
    public function getServerIdFromPath($path) {
        return Crypt::encryptString($path);
    }


    /**
     * Converts the given filePond server id into a path
     *
     * @param string $serverId
     * @return string
     */
    public function getPathFromServerId($serverId) {

        if( !trim($serverId) ) {
            throw new InvalidPathException();
        }

        $filePath = Crypt::decryptString($serverId);

        if( !Str::startsWith( $filePath, realpath(sys_get_temp_dir()) ) ) {
            throw new InvalidPathException();
        }

        return $filePath;
    }



}
