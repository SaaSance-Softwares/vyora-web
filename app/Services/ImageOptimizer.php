<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Store and Optimize an Image.
     * 
     * 1. Reads the file.
     * 2. Converts to WebP.
     * 3. Saves to 'public/products' directory.
     * 4. Returns relative path.
     * 
     * Note: Since we are using Storage::put, Laravel handles the file handling.
     * But to convert, we need to process the stream.
     */
    public function storeAndOptimize(UploadedFile $file, $directory = 'products')
    {
        $filename = Str::uuid() . '.webp';
        $path = $directory . '/' . $filename;

        // Read image
        $image = Image::read($file);

        // Convert to WebP (Quality 80)
        $encoded = $image->toWebp(80);

        // Save to STORAGE (public disk)
        Storage::disk('public')->put($path, (string) $encoded);

        // The original $file is automatically cleaned up by PHP at end of request,
        // so we don't need to explicitly "delete" the temporary upload unless we moved it.

        return $path;
    }
}
