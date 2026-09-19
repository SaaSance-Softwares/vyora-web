<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function faqs()
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }

    protected $appends = ['banner_image_url'];

    public function getBannerImageUrlAttribute()
    {
        $path = $this->banner_image;
        if (! $path) return null;
        if (str_starts_with($path, 'http')) return $path;
        
        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }

}