<?php

namespace App\Exports;

use App\Models\Color;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ColorsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Color::all();
    }

    public function headings(): array
    {
        return ['Name', 'Hex Code'];
    }

    public function map($color): array
    {
        return [
            $color->name,
            $color->hex_code,
        ];
    }
}
