<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryMasterImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_url', 'video_url'];

    public function getImageUrlAttribute()
    {
        $path = $this->image_path;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }

    public function getVideoUrlAttribute()
    {
        $path = $this->video_path;
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
