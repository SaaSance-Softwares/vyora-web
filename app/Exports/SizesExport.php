<?php

namespace App\Exports;

use App\Models\Size;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SizesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Size::all();
    }

    public function headings(): array
    {
        return ['Name', 'Code'];
    }

    public function map($size): array
    {
        return [
            $size->name,
            $size->code,
        ];
    }
}
