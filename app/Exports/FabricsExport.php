<?php

namespace App\Exports;

use App\Models\Fabric;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FabricsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Fabric::all();
    }

    public function headings(): array
    {
        return ['Name'];
    }

    public function map($fabric): array
    {
        return [
            $fabric->name,
        ];
    }
}
