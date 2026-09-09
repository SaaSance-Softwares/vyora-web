<?php

namespace App\Imports;

use App\Models\Fit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FitsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        // Create or update by name since fit only has name
        return Fit::updateOrCreate(
            ['name' => $row['name']],
            ['name' => $row['name']]
        );
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
