<?php

namespace App\Imports;

use App\Models\Fabric;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FabricsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        // Create or update by name since fabric only has name
        return Fabric::updateOrCreate(
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
