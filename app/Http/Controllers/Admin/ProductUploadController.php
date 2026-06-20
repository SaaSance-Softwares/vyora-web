<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QikInkImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductUploadController extends Controller
{
    protected $qikinkImporter;
    protected $generalImporter;

    public function __construct(QikInkImporter $qikinkImporter, \App\Services\GeneralProductImporter $generalImporter)
    {
        $this->qikinkImporter = $qikinkImporter;
        $this->generalImporter = $generalImporter;
    }

    public function index()
    {
        return view('admin.products.upload');
    }

    public function downloadSampleGeneral()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=sample_general.csv",
            "Pragma" => "no-cache"
        ];

        // Comprehensive list of columns based on user request
        $columns = [
            'Product name',
            'Item name',
            'Category',
            'Short description',
            'Long description',
            'Brand name',
            'Attribute Size',
            'Attribute Color',
            'Selling price',
            'Label price',
            'SKU',
            'Stock per sku',
            'Minimum order',
            'Maximum order',
            'Product tag',
            'Seo keyword',
            'Seo title',
            'Seo description',
            'Show in store',
            'Package weight',
            'Package width',
            'Package height',
            'Package length',
            'On sale batch',
            'Qualified for return',
            'Product type',
            'HSN',
            'Tax On Product'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'Cool Hoodie',
                'Cool Hoodie Item',
                'Men,Winter',
                'Best hoodie',
                'Very long desc...',
                'DopeBrand',
                'L',
                'Red',
                '1500',
                '2000',
                'HOOD-RED-L',
                '50',
                '1',
                '10',
                'hoodie,winter',
                'hoodie seo',
                'Seo Title',
                'Desc...',
                'Yes',
                '0.5',
                '10',
                '10',
                '10',
                'No',
                'Yes',
                'Hoodie',
                '6101',
                '12%'
            ]);
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadSampleQikink()
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=sample_qikink.csv",
            "Pragma" => "no-cache",
        ];

        $columns = ['Item name', 'Variant', 'Design SKU', 'Product SKU', 'Store SKU', 'Selling price', 'Product price'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Dope T-Shirt', 'Black - S', 'DES-123', 'PROD-123', 'SKU-BLK-S', '999', '400']);
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'type' => 'required|in:qikink,general'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $count = 0;
        $rowNumber = 1; // Start from 1 (header row is 0)
        $importer = ($request->type === 'general') ? $this->generalImporter : $this->qikinkImporter;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++; // Increment for each data row

            // Basic check to avoid index errors on empty lines
            if (count($row) < count($header))
                continue;

            // Ensure row matches header length for combination
            $row = array_slice($row, 0, count($header));

            $data = array_combine($header, $row);

            try {
                $importer->importRow($data, $rowNumber);
                $count++;
            } catch (\Exception $e) {
                fclose($handle);
                return redirect()
                    ->route('admin.upload', ['tab' => $request->type])
                    ->withErrors(['error' => 'Row ' . $rowNumber . ': ' . $e->getMessage()])
                    ->withInput();
            }
        }

        fclose($handle);

        return redirect()->route('admin.upload', ['tab' => $request->type])->with('success', "Imported {$count} items successfully via {$request->type} mode!");
    }
}
