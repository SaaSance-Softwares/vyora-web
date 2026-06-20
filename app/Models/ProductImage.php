<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',
        'attribute_value_id',
        'image_path',
        'media_type',
        'is_primary',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function getUrlAttribute()
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
}
