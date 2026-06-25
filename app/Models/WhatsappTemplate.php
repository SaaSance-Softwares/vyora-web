<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language',
        'status',
        'category',
        'components',
        'variables_mapping',
    ];

    protected $casts = [
        'components' => 'array',
        'variables_mapping' => 'array',
    ];
}
