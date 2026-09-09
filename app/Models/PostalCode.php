<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostalCode extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'postal_code', 'city', 'district', 'state'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
