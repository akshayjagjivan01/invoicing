<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use App\Models\Specification;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query()->with(['brand', 'categories']);

        // Apply filters
        if ($request->has('brand_id') && $request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('category_id') && $request->category_id) {
            $categoryIds = Category::findOrFail($request->category_id)
                          ->descendants()
                          ->pluck('id')
                          ->push($request->category_id)
                          ->toArray();

            $query->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSortFields = ['id', 'name', 'sku', 'base_price', 'created_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate(10)->withQueryString();

        // Get filters for the sidebar
        $brands = Brand::orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'is_active' => 'required|boolean',
            'brand_id' => 'nullable|exists:brands,id',
            'size_id' => 'nullable|exists:sizes,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'specifications' => 'nullable|array',
            'specifications.*.id' => 'required|exists:specifications,id',
            'specifications.*.value' => 'required|string',
        ]);

        // Create the product
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
            'sku' => $validated['sku'],
            'is_active' => $validated['is_active'],
            'brand_id' => $validated['brand_id'],
            'size_id' => $validated['size_id'],
        ]);

        // Attach categories if provided
        if (isset($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        // Attach specifications if provided
        if (isset($validated['specifications']) && is_array($validated['specifications'])) {
            $specs = [];
            foreach ($validated['specifications'] as $spec) {
                if (!empty($spec['id']) && !empty($spec['value'])) {
                    $specs[$spec['id']] = ['value' => $spec['value']];
                }
            }

            $product->specifications()->sync($specs);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['brand', 'size', 'categories', 'specifications', 'sales']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load(['brand', 'size', 'categories', 'specifications']);
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'is_active' => 'required|boolean',
            'brand_id' => 'nullable|exists:brands,id',
            'size_id' => 'nullable|exists:sizes,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'specifications' => 'nullable|array',
            'specifications.*.id' => 'required|exists:specifications,id',
            'specifications.*.value' => 'required|string',
        ]);

        // Update the product
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
            'sku' => $validated['sku'],
            'is_active' => $validated['is_active'],
            'brand_id' => $validated['brand_id'],
            'size_id' => $validated['size_id'],
        ]);

        // Sync categories if provided
        if (isset($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        } else {
            $product->categories()->detach();
        }

        // Sync specifications if provided
        if (isset($validated['specifications']) && is_array($validated['specifications'])) {
            $specs = [];
            foreach ($validated['specifications'] as $spec) {
                if (!empty($spec['id']) && !empty($spec['value'])) {
                    $specs[$spec['id']] = ['value' => $spec['value']];
                }
            }

            $product->specifications()->sync($specs);
        } else {
            $product->specifications()->detach();
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

/**
 * Create a quick product during sale creation.
 */
public function addQuickProduct(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'base_price' => 'required|numeric|min:0',
    ]);

    // Create a temporary product marked as custom/adhoc
    $product = Product::create([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? 'Custom product created during sale',
        'base_price' => $validated['base_price'],
        'sku' => 'CUSTOM-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'is_active' => true, // Make it active by default
        'is_custom' => true, // You can use an existing boolean field or add this if it exists
    ]);

    return response()->json([
        'success' => true,
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'base_price' => $product->base_price,
            'sku' => $product->sku
        ]
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if the product is used in any sales
        if ($product->sales()->exists()) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Cannot delete this product because it is used in one or more sales.');
        }

        $product->categories()->detach();
        $product->specifications()->detach();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Get products for autocomplete.
     */
    public function autocomplete(Request $request)
    {
        $term = $request->input('term');

        $products = Product::where('is_active', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                      ->orWhere('sku', 'LIKE', "%{$term}%");
            })
            ->select('id', 'name', 'base_price', 'sku')
            ->with(['brand', 'size'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        $result = [];

        foreach ($products as $product) {
            $brandName = $product->brand ? $product->brand->name . ' - ' : '';
            $sizeName = $product->size ? ' (' . $product->size->name . ')' : '';
            $skuText = $product->sku ? ' [' . $product->sku . ']' : '';

            $result[] = [
                'id' => $product->id,
                'value' => $product->name,
                'label' => $brandName . $product->name . $sizeName . $skuText . ' - R' . number_format($product->base_price, 2),
                'base_price' => $product->base_price
            ];
        }

        return response()->json($result);
    }
}
