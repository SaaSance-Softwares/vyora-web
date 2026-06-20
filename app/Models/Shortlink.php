<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shortlink extends Model
{
    protected $fillable = [
        'product_id',
        'short_code',
        'actual_link',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'click_count',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getFullActualLinkAttribute()
    {
        $url = $this->actual_link;
        $params = [];
        if ($this->utm_source) $params['utm_source'] = $this->utm_source;
        if ($this->utm_medium) $params['utm_medium'] = $this->utm_medium;
        if ($this->utm_campaign) $params['utm_campaign'] = $this->utm_campaign;
        if ($this->utm_term) $params['utm_term'] = $this->utm_term;
        if ($this->utm_content) $params['utm_content'] = $this->utm_content;

        if (!empty($params)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($params);
        }

        return $url;
    }
}
