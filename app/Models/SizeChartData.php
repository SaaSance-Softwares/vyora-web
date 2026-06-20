<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizeChartData extends Model
{
    use HasFactory;

    protected $table = 'size_chart_data';

    protected $guarded = [];

    protected $casts = [
        'table_data' => 'array',
    ];

    // Handle legacy double-encoded JSON data
    public function getTableDataAttribute($value)
    {
        // If it's already decoded as an array by the cast, return it
        if (is_array($value)) {
            return $value;
        }

        // If it's a string, it's legacy double-encoded JSON - decode it
        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function sizeChart()
    {
        return $this->belongsTo(SizeChart::class);
    }
}
