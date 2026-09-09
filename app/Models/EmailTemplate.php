<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'variables',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
        'status' => 'boolean',
    ];

    public function orderStatuses()
    {
        return $this->hasMany(OrderStatus::class, 'email_template_id');
    }
}
