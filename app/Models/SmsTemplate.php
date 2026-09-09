<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'variables',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
        'status' => 'boolean',
    ];

    public function orderStatuses()
    {
        return $this->hasMany(OrderStatus::class, 'sms_template_id');
    }
}
