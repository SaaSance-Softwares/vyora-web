<?php

namespace App\Exports;

use App\Models\Fit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FitsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Fit::all();
    }

    public function headings(): array
    {
        return ['Name'];
    }

    public function map($fit): array
    {
        return [
            $fit->name,
        ];
    }
}
