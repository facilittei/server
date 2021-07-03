<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Services\StorageServiceContract;

class StorageServiceS3 implements StorageServiceContract
{
    /**
     * Upload file to storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $file
     * @param string $destination
     */
    public function upload($request, $filename, $destination) 
    {
        return $request->file($filename)->storePublicly($destination, 's3');
    }

    /**
     * Destroy a file from storage
     * 
     * @param string $file
     */
    public function destroy($filename)
    {
        Storage::disk('s3')->delete(str_replace(config('app.assets_url') . '/', '', $filename));
    }

}
