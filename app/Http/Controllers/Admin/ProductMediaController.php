<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategoryMasterImage;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductMediaController extends Controller
{
    public function uploadMasterPreview(Request $request, Product $product)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($product->preview_image) {
            $oldPath = public_path("/{$product->preview_image}");
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $relativePath = 'uploads/products/preview'; // Use uploads for consistency

        $backendPath = public_path($relativePath);

        // Create directories
        if (! file_exists($backendPath)) {
            mkdir($backendPath, 0755, true);
        }

        // Move to backend
        $file->move($backendPath, $fileName);

        $product->update(['preview_image' => "/{$relativePath}/{$fileName}"]);

        return response()->json([
            'success' => true,
            'url' => asset($product->preview_image),
        ]);
    }

    public function uploadCategoryMasterPreview(Request $request, Product $product)
    {
        $type = $request->type;
        $allowedMimes = $type === 'video' ? 'mimes:mp4,mov,qt,webm' : 'mimes:jpeg,png,jpg,gif,webp';

        $request->validate([
            'file' => "required|file|{$allowedMimes}|max:51200",
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:image,video',
        ]);

        $catImage = ProductCategoryMasterImage::where('product_id', $product->id)
            ->where('category_id', $request->category_id)->first();

        $type = $request->type;
        $pathField = $type === 'video' ? 'video_path' : 'image_path';

        if ($catImage && $catImage->$pathField) {
            $oldPath = public_path("{$catImage->$pathField}");
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $isVideo = in_array($extension, ['mp4', 'mov', 'qt', 'webm']);

        $relativePath = 'uploads/products/preview/category_'.$request->category_id;
        $backendPath = public_path($relativePath);

        if (! file_exists($backendPath)) {
            mkdir($backendPath, 0755, true);
        }

        if ($isVideo) {
            $fileName = time().'_'.pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.webm';
            $tempPath = $file->getRealPath();
            $backendDest = $backendPath.'/'.$fileName;

            if ($extension !== 'webm') {
                // Try different common ffmpeg paths for macOS/Linux compatibility
                $ffmpegPaths = ['ffmpeg', '/opt/homebrew/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/usr/bin/ffmpeg'];
                $conversionSuccess = false;

                foreach ($ffmpegPaths as $ffmpegPath) {
                    $ffmpegCmd = "{$ffmpegPath} -y -i ".escapeshellarg($tempPath).' -c:v libvpx-vp9 -crf 30 -b:v 0 -b:a 128k -c:a libopus '.escapeshellarg($backendDest).' 2>&1';
                    exec($ffmpegCmd, $output, $returnCode);
                    if ($returnCode === 0) {
                        $conversionSuccess = true;
                        break;
                    }
                }

                if ($conversionSuccess) {
                    // copy($backendDest, $frontendDest);
                } else {
                    // Fallback to original if conversion fails
                    $fileName = time().'_'.$file->getClientOriginalName();
                    $file->move($backendPath, $fileName);
                    \Log::error('FFmpeg conversion failed: '.implode("\n", $output));
                }
            } else {
                $file->move($backendPath, $fileName);
            }
        } else {
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->move($backendPath, $fileName);
        }

        ProductCategoryMasterImage::updateOrCreate(
            ['product_id' => $product->id, 'category_id' => $request->category_id],
            [$pathField => "/{$relativePath}/{$fileName}"]
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function deleteCategoryMasterPreview(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:image,video',
        ]);

        $catImage = ProductCategoryMasterImage::where('product_id', $product->id)
            ->where('category_id', $request->category_id)->first();

        if (! $catImage) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $type = $request->type;
        $pathField = $type === 'video' ? 'video_path' : 'image_path';

        if ($catImage->$pathField) {
            $oldPath = public_path("{$catImage->$pathField}");
            $backendPath = public_path($catImage->$pathField);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            if (file_exists($backendPath)) {
                unlink($backendPath);
            }

            $catImage->$pathField = null;
            if (! $catImage->image_path && ! $catImage->video_path) {
                $catImage->delete();
            } else {
                $catImage->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function upload(Request $request, Product $product)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:jpeg,jpg,png,webp,mp4,mov,avi|max:51200', // 50MB max
            'color_id' => 'required|exists:colors,id',
        ]);

        $uploadedMedia = [];

        foreach ($request->file('files') as $file) {
            $extension = $file->getClientOriginalExtension();
            $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi']);

            if ($isVideo) {
                // Handle video upload and convert to WebM
                $fileName = uniqid().'.webm';
                $relativePath = "uploads/products/{$product->id}/colors/{$request->color_id}";
                $backendPath = public_path($relativePath);

                if (! file_exists($backendPath)) {
                    mkdir($backendPath, 0755, true);
                }

                $tempPath = $file->getRealPath();
                $backendDest = $backendPath.'/'.$fileName;

                if (strtolower($extension) !== 'webm') {
                    // Try different common ffmpeg paths for macOS/Linux compatibility
                    $ffmpegPaths = ['ffmpeg', '/opt/homebrew/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/usr/bin/ffmpeg'];
                    $conversionSuccess = false;

                    foreach ($ffmpegPaths as $ffmpegPath) {
                        $ffmpegCmd = "{$ffmpegPath} -y -i ".escapeshellarg($tempPath).' -c:v libvpx-vp9 -crf 30 -b:v 0 -b:a 128k -c:a libopus '.escapeshellarg($backendDest).' 2>&1';
                        exec($ffmpegCmd, $output, $returnCode);
                        if ($returnCode === 0) {
                            $conversionSuccess = true;
                            break;
                        }
                    }

                    if ($conversionSuccess) {
                        // copy($backendDest, $frontendDest);
                    } else {
                        // Fallback to original if conversion fails
                        $fileName = uniqid().'.'.$extension;
                        $file->move($backendPath, $fileName);
                        \Log::error('FFmpeg conversion failed: '.implode("\n", $output));
                    }
                } else {
                    $file->move($backendPath, $fileName);
                }

                $media = ProductImage::create([
                    'product_id' => $product->id,
                    'color_id' => $request->color_id,
                    'image_path' => "/{$relativePath}/{$fileName}",
                    'media_type' => 'video',
                    'is_primary' => false,
                ]);
            } else {
                // Handle image upload with WebP conversion
                $fileName = uniqid().'.webp';
                $relativePath = "uploads/products/{$product->id}/colors/{$request->color_id}";
                $backendPath = public_path($relativePath);

                if (! file_exists($backendPath)) {
                    mkdir($backendPath, 0755, true);
                }

                try {
                    // Save original first to backend
                    $file->move($backendPath, $fileName);

                    // Optional WebP conversion
                    try {
                        $image = Image::read($backendPath.'/'.$fileName);
                        $image->toWebp(85)->save($backendPath.'/'.$fileName);

                    } catch (\Exception $e) {
                        \Log::warning('WebP conversion failed: '.$e->getMessage());
                    }
                } catch (\Exception $e) {
                    \Log::error('Product media upload failed: '.$e->getMessage());

                    continue; // Skip this file
                }

                $media = ProductImage::create([
                    'product_id' => $product->id,
                    'color_id' => $request->color_id,
                    'image_path' => "/{$relativePath}/{$fileName}",
                    'media_type' => 'image',
                    'is_primary' => false,
                ]);
            }

            $uploadedMedia[] = [
                'id' => $media->id,
                'url' => $media->url,
                'media_type' => $media->media_type,
                'is_primary' => $media->is_primary,
            ];
        }

        return response()->json([
            'success' => true,
            'media' => $uploadedMedia,
        ]);
    }

    public function delete(Product $product, ProductImage $productImage)
    {
        // Ensure the image belongs to the product
        if ($productImage->product_id !== $product->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete file from storage
        $fullPath = public_path("/{$productImage->image_path}");
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete database record
        $productImage->delete();

        return response()->json(['success' => true]);
    }

    public function setPrimary(Product $product, ProductImage $productImage)
    {
        // Ensure the image belongs to the product
        if ($productImage->product_id !== $product->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Unset all primary images for this product and color
        ProductImage::where('product_id', $product->id)
            ->where('color_id', $productImage->color_id)
            ->update(['is_primary' => false]);

        // Set this image as primary
        $productImage->update(['is_primary' => true]);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Product $product)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'required|exists:product_images,id',
        ]);

        foreach ($request->media_ids as $index => $id) {
            ProductImage::where('id', $id)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
