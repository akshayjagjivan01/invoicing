<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(10);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // 2MB max
            'is_active' => 'required|boolean',
        ]);

        $brand = new Brand();
        $brand->name = $validated['name'];
        $brand->description = $validated['description'];
        $brand->is_active = $validated['is_active'];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $brand->logo_path = $path;
        }

        $brand->save();

        return redirect()->route('admin.brands.index')
                         ->with('success', 'Brand created successfully.');
    }

    public function show(Brand $brand)
    {
        $brand->loadCount('products');
        $products = $brand->products()->paginate(10);
        return view('admin.brands.show', compact('brand', 'products'));
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048', // 2MB max
            'is_active' => 'required|boolean',
        ]);

        $brand->name = $validated['name'];
        $brand->description = $validated['description'];
        $brand->is_active = $validated['is_active'];

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            // Store new logo
            $path = $request->file('logo')->store('brands', 'public');
            $brand->logo_path = $path;
        }

        $brand->save();

        return redirect()->route('admin.brands.index')
                         ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        // Check if brand has products
        if ($brand->products()->exists()) {
            return redirect()->route('admin.brands.index')
                             ->with('error', 'Cannot delete brand because it has associated products.');
        }

        // Delete logo if exists
        if ($brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
                         ->with('success', 'Brand deleted successfully.');
    }
}
