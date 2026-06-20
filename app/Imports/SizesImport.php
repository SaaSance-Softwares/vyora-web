<?php

namespace App\Imports;

use App\Models\Size;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SizesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['name']) || empty($row['code'])) {
            return null;
        }

        // Create or update by code (since code is usually unique like XL, XXL)
        return Size::updateOrCreate(
            ['code' => $row['code']],
            ['name' => $row['name']]
        );
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|max:255',
        ];
    }
}
