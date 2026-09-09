<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_days',
        'max_days',
        'internal_note',
        'show_to_user',
        'is_default',
    ];
}
