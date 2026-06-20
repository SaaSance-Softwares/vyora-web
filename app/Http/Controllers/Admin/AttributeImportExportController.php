<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ColorsExport;
use App\Exports\ProductTypesExport;
use App\Exports\SizesExport;
use App\Http\Controllers\Controller;
use App\Imports\ColorsImport;
use App\Imports\ProductTypesImport;
use App\Imports\SizesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttributeImportExportController extends Controller
{
    private $mappers = [
        'colors' => [
            'export' => ColorsExport::class,
            'import' => ColorsImport::class,
            'tab' => 'colors',
            'sample' => [
                ['Name', 'Hex Code'],
                ['Red', '#ff0000'],
                ['Blue', '#0000ff'],
            ],
        ],
        'sizes' => [
            'export' => SizesExport::class,
            'import' => SizesImport::class,
            'tab' => 'sizes',
            'sample' => [
                ['Name', 'Code'],
                ['Small', 'S'],
                ['Large', 'L'],
            ],
        ],
        'hsn' => [
            'export' => ProductTypesExport::class,
            'import' => ProductTypesImport::class,
            'tab' => 'hsn',
            'sample' => [
                ['Type Name', 'HSN Code'],
                ['T-Shirts', '61091000'],
                ['Jeans', '62034200'],
            ],
        ],
    ];

    public function export($type)
    {
        if (! array_key_exists($type, $this->mappers)) {
            abort(404);
        }

        $exportClass = $this->mappers[$type]['export'];

        return Excel::download(new $exportClass, "{$type}_export.xlsx");
    }

    public function import(Request $request, $type)
    {
        if (! array_key_exists($type, $this->mappers)) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:10240', // max 10mb
        ]);

        try {
            $importClass = $this->mappers[$type]['import'];
            Excel::import(new $importClass, $request->file('file'));

            return redirect()->route('admin.attributes.index')
                ->withFragment($this->mappers[$type]['tab'])
                ->with('success', ucfirst($type).' imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.attributes.index')
                ->withFragment($this->mappers[$type]['tab'])
                ->with('error', 'Error importing file: '.$e->getMessage());
        }
    }

    public function sample($type)
    {
        if (! array_key_exists($type, $this->mappers)) {
            abort(404);
        }

        $sampleData = $this->mappers[$type]['sample'];

        $callback = function () use ($sampleData) {
            $file = fopen('php://output', 'w');
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$type}_sample.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
