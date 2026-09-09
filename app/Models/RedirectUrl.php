<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedirectUrl extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::saving(function ($redirect) {
            if ($redirect->old_url) {
                $oldUrl = trim($redirect->old_url);
                if (str_starts_with($oldUrl, 'http')) {
                    $parsed = parse_url($oldUrl, PHP_URL_PATH);
                    $query = parse_url($oldUrl, PHP_URL_QUERY);
                    $oldUrl = $parsed . ($query ? '?' . $query : '');
                }
                $oldUrl = ltrim($oldUrl, '/');
                $redirect->old_url = $oldUrl;
            }
        });
    }
}
