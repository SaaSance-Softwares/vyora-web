<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPageLog extends Model
{
    protected $fillable = [
        'legal_page_id',
        'user_id',
        'action',
        'content_snapshot',
    ];

    public function page()
    {
        return $this->belongsTo(LegalPage::class, 'legal_page_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
