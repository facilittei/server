<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

interface StorageServiceContract {
    /**
     * Upload file to storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $file
     * @param string $destination
     */
    public function upload($request, $file, $destination);

    /**
     * Destroy a file from storage
     * 
     * @param string $file
     */
    public function destroy($filename);
}