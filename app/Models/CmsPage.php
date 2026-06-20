<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'layout',
        'content',
        'meta_title',
        'meta_description',
        'meta_image',
        'is_active',
        'is_home',
        'draft_content',
    ];

    protected $casts = [
        'content' => 'array',
        'draft_content' => 'array',
        'is_active' => 'boolean',
        'is_home' => 'boolean',
    ];
}
