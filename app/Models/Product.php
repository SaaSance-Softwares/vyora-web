<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'brand_name' => $this->brand_name,
            'tags' => $this->tags,
        ];
    }

    protected $guarded = [];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        $path = $this->preview_image;
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        return asset('storage/'.$cleanPath);
    }

    public function skus()
    {
        return $this->hasMany(Sku::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product');
    }

    public function sizeChart()
    {
        return $this->belongsToMany(SizeChart::class, 'product_size_chart');
    }

    public function categoryMasterImages()
    {
        return $this->hasMany(ProductCategoryMasterImage::class);
    }

    public function shortlinks()
    {
        return $this->hasMany(Shortlink::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
