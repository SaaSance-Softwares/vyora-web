<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'bogo_buy_qty',
        'bogo_get_qty',
        'bogo_max_discount',
        'discount_amount',
        'max_discount_amount',
        'is_active',
        'is_default_magic',
        'show_on_product_page',
        'show_on_checkout_page',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_limit_per_user',
        'times_used',
        'min_cart_value',
        'min_item_quantity',
        'exclude_sale_items',
        'first_time_users_only',
        'can_combine',
        'applicable_product_ids',
        'applicable_category_ids',
        'excluded_product_ids',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default_magic' => 'boolean',
        'show_on_product_page' => 'boolean',
        'show_on_checkout_page' => 'boolean',
        'exclude_sale_items' => 'boolean',
        'first_time_users_only' => 'boolean',
        'can_combine' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_product_ids' => 'array',
        'applicable_category_ids' => 'array',
        'excluded_product_ids' => 'array',
    ];
}
