<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sku) {
            if (empty($sku->short_code)) {
                do {
                    $short = 'SKU-' . mt_rand(10000000, 99999999);
                } while (self::where('short_code', $short)->exists());
                $sku->short_code = $short;
            }
        });
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'sku_attribute_values');
    }
}
