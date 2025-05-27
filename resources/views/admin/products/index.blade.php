@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Filter sidebar -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <i class="fas fa-filter me-2"></i>{{ __('Filters') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.index') }}" method="GET" id="filter-form">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="Name, SKU, or description">
                        </div>

                        <div class="mb-3">
                            <label for="brand_id" class="form-label">Brand</label>
                            <select class="form-control form-control-sm" id="brand_id" name="brand_id">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-control form-control-sm" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&mdash; {{ $child->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control form-control-sm" id="is_active" name="is_active">
                                <option value="">All</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort by</label>
                            <select class="form-control form-control-sm" id="sort" name="sort">
                                <option value="id" {{ request('sort', 'id') == 'id' ? 'selected' : '' }}>ID</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                                <option value="base_price" {{ request('sort') == 'base_price' ? 'selected' : '' }}>Price</option>
                                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="direction" class="form-label">Order</label>
                            <select class="form-control form-control-sm" id="direction" name="direction">
                                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <i class="fas fa-cog me-2"></i>{{ __('Manage Filters') }}
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.brands.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-tag me-2"></i> Manage Brands
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-folder me-2"></i> Manage Categories
                        </a>
                        <a href="{{ route('admin.specifications.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list-ul me-2"></i> Manage Specifications
                        </a>
                        <a href="{{ route('admin.sizes.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-ruler me-2"></i> Manage Sizes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-boxes me-2"></i>{{ __('Products Management') }}</span>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus me-1"></i> New Product
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @include('components.flash-messages')

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Brand</th>
                                    <th>Categories</th>
                                    <th>Base Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            @if($product->sku)
                                                <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                        <td>
                                            @foreach($product->categories as $category)
                                                <span class="badge bg-secondary">{{ $category->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>R{{ number_format($product->base_price, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-info btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" data-confirm="Are you sure you want to delete this product?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No products found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                        </div>
                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-submit the form when select filters change
        $('#brand_id, #category_id, #is_active, #sort, #direction').change(function() {
            $('#filter-form').submit();
        });
    });
</script>
@endsection
