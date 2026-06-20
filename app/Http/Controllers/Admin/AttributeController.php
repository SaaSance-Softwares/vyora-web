<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Color;
use App\Models\ProductType;
use App\Models\Size;
use App\Models\SizeChart;

class AttributeController extends Controller
{
    public function index()
    {
        $colors = Color::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();
        $types = ProductType::orderBy('name')->get();
        $sizeCharts = SizeChart::withCount('products')->latest()->get();

        return view('admin.attributes.index', compact('colors', 'sizes', 'types', 'sizeCharts'));
    }


    public function storeSize(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:sizes,code',
        ]);

        \App\Models\Size::create($request->all());

        return redirect()->route('admin.attributes.index')->withFragment('sizes')->with('success', 'Size added successfully.');
    }

    public function updateSize(Request $request, \App\Models\Size $size)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:sizes,code,' . $size->id,
        ]);

        $size->update($request->all());

        return redirect()->route('admin.attributes.index')->withFragment('sizes')->with('success', 'Size updated successfully.');
    }

    public function destroySize(\App\Models\Size $size)
    {
        $size->delete();
        return redirect()->route('admin.attributes.index')->withFragment('sizes')->with('success', 'Size deleted successfully.');
    }

    public function storeColor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => 'required|string|max:7',
        ]);

        \App\Models\Color::create($request->all());

        return redirect()->route('admin.attributes.index')->withFragment('colors')->with('success', 'Color added successfully.');
    }

    public function updateColor(Request $request, \App\Models\Color $color)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => 'required|string|max:7',
        ]);

        $color->update($request->all());

        return redirect()->route('admin.attributes.index')->withFragment('colors')->with('success', 'Color updated successfully.');
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hsn_code' => 'required|string|max:255',
        ]);

        \App\Models\ProductType::create($request->all());

        return redirect()->route('admin.attributes.index')->withFragment('hsn')->with('success', 'Product Type added successfully.');
    }

    public function updateType(Request $request, \App\Models\ProductType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hsn_code' => 'required|string|max:255',
        ]);

        $type->update($request->all());

        return redirect()->route('admin.attributes.index')->withFragment('hsn')->with('success', 'Product Type updated successfully.');
    }

    public function destroyColor(\App\Models\Color $color)
    {
        $color->delete();
        return redirect()->route('admin.attributes.index')->withFragment('colors')->with('success', 'Color deleted successfully.');
    }

    public function destroyType(\App\Models\ProductType $type)
    {
        $type->delete();
        return redirect()->route('admin.attributes.index')->withFragment('hsn')->with('success', 'Product Type deleted successfully.');
    }
}
