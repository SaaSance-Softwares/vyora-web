<?php

namespace App\Imports;

use App\Models\Color;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ColorsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['name']) || empty($row['hex_code'])) {
            return null;
        }

        // Create or update by name
        return Color::updateOrCreate(
            ['name' => $row['name']],
            ['hex_code' => $row['hex_code']]
        );
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'hex_code' => 'required|string|max:7',
        ];
    }
}
