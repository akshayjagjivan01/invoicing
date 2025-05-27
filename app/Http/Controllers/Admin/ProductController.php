<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use App\Models\Specification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products with optional filtering.
     */
    public function index(Request $request)
    {
        $query = Product::query()->with(['brand', 'categories']);

        // Apply filters
        if ($request->has('brand_id') && $request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->has('category_id') && $request->category_id) {
            $categoryIds = [];

            if ($request->category_id) {
                $category = Category::find($request->category_id);
                if ($category) {
                    // Include the selected category and all its children
                    $categoryIds = Category::where('parent_id', $category->id)
                                ->pluck('id')
                                ->push($category->id)
                                ->toArray();
                }
            }

            if (!empty($categoryIds)) {
                $query->whereHas('categories', function($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active == 1);
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
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Get data for dropdowns
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $sizes = Size::orderBy('dimension_type')->orderBy('sort_order')->orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $specifications = Specification::orderBy('group')->orderBy('name')->get();

        return view('admin.products.create', compact('brands', 'sizes', 'categories', 'specifications'));
    }

    /**
     * Store a newly created product in storage.
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
            'specifications.*.id' => 'nullable|exists:specifications,id',
            'specifications.*.value' => 'nullable|string|max:255',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Create the product
        $product = new Product();
        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? null;
        $product->base_price = $validated['base_price'];
        $product->sku = $validated['sku'] ?? null;
        $product->is_active = $validated['is_active'];
        $product->brand_id = $validated['brand_id'] ?? null;
        $product->size_id = $validated['size_id'] ?? null;
        $product->save();

        // Handle image upload if present
        if ($request->hasFile('product_image')) {
            $path = $request->file('product_image')->store('products', 'public');
            $product->image_path = $path;
            $product->save();
        }

        // Attach categories if provided
        if (isset($validated['categories']) && is_array($validated['categories'])) {
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

            if (!empty($specs)) {
                $product->specifications()->sync($specs);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Load relationships for display
        $product->load(['brand', 'size', 'categories', 'specifications', 'sales' => function($query) {
            $query->take(10)->latest();
        }]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Load the product with its relationships
        $product->load(['brand', 'size', 'categories', 'specifications']);

        // Get data for dropdowns
        $brands = Brand::orderBy('name')->get();
        $sizes = Size::orderBy('dimension_type')->orderBy('sort_order')->orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $specifications = Specification::orderBy('group')->orderBy('name')->get();

        // Get currently selected categories IDs for pre-selecting in form
        $selectedCategoryIds = $product->categories->pluck('id')->toArray();

        return view('admin.products.edit', compact(
            'product', 'brands', 'sizes', 'categories', 'specifications', 'selectedCategoryIds'
        ));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products')->ignore($product->id),
            ],
            'is_active' => 'required|boolean',
            'brand_id' => 'nullable|exists:brands,id',
            'size_id' => 'nullable|exists:sizes,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'specifications' => 'nullable|array',
            'specifications.*.id' => 'nullable|exists:specifications,id',
            'specifications.*.value' => 'nullable|string|max:255',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        // Update the product
        $product->name = $validated['name'];
        $product->description = $validated['description'] ?? null;
        $product->base_price = $validated['base_price'];
        $product->sku = $validated['sku'] ?? null;
        $product->is_active = $validated['is_active'];
        $product->brand_id = $validated['brand_id'] ?? null;
        $product->size_id = $validated['size_id'] ?? null;

        // Handle image upload if present
        if ($request->hasFile('product_image')) {
            // Delete old image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $path = $request->file('product_image')->store('products', 'public');
            $product->image_path = $path;
        }
        // Remove image if requested
        elseif (isset($validated['remove_image']) && $validated['remove_image'] && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $product->image_path = null;
        }

        $product->save();

        // Sync categories
        if (isset($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        } else {
            $product->categories()->detach();
        }

        // Sync specifications if provided
        if (isset($validated['specifications']) && is_array($validated['specifications'])) {
            $specs = [];
            foreach ($validated['specifications'] as $spec) {
                if (!empty($spec['id']) && isset($spec['value'])) {
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
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Check if the product is used in any sales
        if ($product->sales()->exists()) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Cannot delete this product because it is used in one or more sales.');
        }

        // Delete product image if exists
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        // Detach all relationships before deleting
        $product->categories()->detach();
        $product->specifications()->detach();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Get products for autocomplete in sales forms.
     */
    public function autocomplete(Request $request)
    {
        $term = $request->input('term');

        $products = Product::where('is_active', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                      ->orWhere('sku', 'LIKE', "%{$term}%");
            })
            ->select('id', 'name', 'base_price', 'sku', 'brand_id', 'size_id')
            ->with(['brand:id,name', 'size:id,name,value'])
            ->orderBy('name')
            ->limit(15)
            ->get();

        $result = [];

        foreach ($products as $product) {
            $brandName = $product->brand ? $product->brand->name . ' - ' : '';
            $sizeName = $product->size ? ' (' . $product->size->value . ')' : '';
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

    /**
     * Get a list of low stock products for dashboard alerts.
     */
    public function lowStock()
    {
        // This method could be expanded if you add inventory tracking
        $products = Product::where('is_active', true)
                          ->whereColumn('stock', '<', 'min_stock_level')
                          ->get();

        return response()->json($products);
    }

    /**
     * Bulk update product status.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        $count = 0;

        switch ($validated['action']) {
            case 'activate':
                $count = Product::whereIn('id', $validated['product_ids'])->update(['is_active' => true]);
                $message = "{$count} products activated successfully.";
                break;

            case 'deactivate':
                $count = Product::whereIn('id', $validated['product_ids'])->update(['is_active' => false]);
                $message = "{$count} products deactivated successfully.";
                break;

            case 'delete':
                // Check if any products are used in sales
                $usedProducts = Product::whereIn('id', $validated['product_ids'])
                                      ->whereHas('sales')
                                      ->pluck('name')
                                      ->toArray();

                if (!empty($usedProducts)) {
                    return redirect()->route('admin.products.index')
                        ->with('error', 'Cannot delete products: ' . implode(', ', $usedProducts) . ' because they are used in sales.');
                }

                $count = Product::whereIn('id', $validated['product_ids'])->delete();
                $message = "{$count} products deleted successfully.";
                break;
        }

        return redirect()->route('admin.products.index')->with('success', $message);
    }
}
