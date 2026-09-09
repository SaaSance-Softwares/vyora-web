<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_mandatory',
        'is_published',
        'meta_title',
        'meta_description',
        'meta_image',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(LegalPageLog::class)->latest();
    }
}
