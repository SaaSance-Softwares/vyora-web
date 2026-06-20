<?php

namespace App\Exports;

use App\Models\ProductType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductTypesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ProductType::all();
    }

    public function headings(): array
    {
        return ['Type Name', 'HSN Code'];
    }

    public function map($type): array
    {
        return [
            $type->name,
            $type->hsn_code,
        ];
    }
}
