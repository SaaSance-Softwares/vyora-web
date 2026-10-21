<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequestItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function returnRequest()
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function exchangeVariant()
    {
        return $this->belongsTo(Sku::class, 'exchange_variant_id');
    }
}
