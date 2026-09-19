<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'faqable_id',
        'faqable_type',
        'question',
        'answer',
        'sort_order',
    ];

    public function faqable()
    {
        return $this->morphTo();
    }
}
