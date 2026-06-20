<?php

namespace App\Imports;

use App\Models\ProductType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductTypesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['type_name']) || empty($row['hsn_code'])) {
            return null;
        }

        // Create or update by name
        return ProductType::updateOrCreate(
            ['name' => $row['type_name']],
            ['hsn_code' => $row['hsn_code']]
        );
    }

    public function rules(): array
    {
        return [
            'type_name' => 'required|string|max:255',
            'hsn_code' => 'required|max:255',
        ];
    }
}
