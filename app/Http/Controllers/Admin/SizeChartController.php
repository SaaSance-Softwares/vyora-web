<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeChart;
use App\Models\SizeChartData;
use Illuminate\Http\Request;

class SizeChartController extends Controller
{
    public function index()
    {
        $sizeCharts = SizeChart::withCount('products')->latest()->get();
        return view('admin.size-charts.index', compact('sizeCharts'));
    }

    public function create()
    {
        $sizes = \App\Models\Size::all();
        return view('admin.size-charts.create', compact('sizes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'table_data' => 'required|json',
            'unit' => 'required|in:inches,cm',
        ]);

        // Create size chart
        $sizeChart = SizeChart::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Create size chart data
        SizeChartData::create([
            'size_chart_id' => $sizeChart->id,
            'table_data' => json_decode($request->table_data, true), // Decode JSON string to array
            'unit' => $request->unit,
        ]);

        return redirect()->route('admin.attributes.index')->withFragment('size-chart')->with('success', 'Size chart created successfully.');
    }

    public function edit(SizeChart $sizeChart)
    {
        $sizeChart->load('data', 'products');
        $sizes = \App\Models\Size::all();
        return view('admin.size-charts.edit', compact('sizeChart', 'sizes'));
    }

    public function update(Request $request, SizeChart $sizeChart)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'table_data' => 'required|json',
            'unit' => 'required|in:inches,cm',
        ]);

        // Update size chart
        $sizeChart->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Update size chart data
        if ($sizeChart->data) {
            $sizeChart->data->update([
                'table_data' => json_decode($request->table_data, true), // Decode JSON string to array
                'unit' => $request->unit,
            ]);
        } else {
            SizeChartData::create([
                'size_chart_id' => $sizeChart->id,
                'table_data' => json_decode($request->table_data, true), // Decode JSON string to array
                'unit' => $request->unit,
            ]);
        }

        return redirect()->route('admin.attributes.index')->withFragment('size-chart')->with('success', 'Size chart updated successfully.');
    }

    public function destroy(SizeChart $sizeChart)
    {
        $sizeChart->delete();
        return redirect()->route('admin.attributes.index')->withFragment('size-chart')->with('success', 'Size chart deleted successfully.');
    }
}
