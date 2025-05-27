@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-plus-circle me-2"></i>{{ __('Create New Product') }}</span>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Products
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @include('components.flash-messages')

                    <form method="POST" action="{{ route('admin.products.store') }}" class="fade-in" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sku" class="form-label fw-bold">{{ __('SKU') }}</label>
                                    <input id="sku" type="text" class="form-control @error('sku') is-invalid @enderror" name="sku" value="{{ old('sku') }}">
                                    <small class="form-text text-muted">Optional unique identifier</small>
                                    @error('sku')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="brand_id" class="form-label fw-bold">{{ __('Brand') }}</label>
                                    <select id="brand_id" class="form-control @error('brand_id') is-invalid @enderror" name="brand_id">
                                        <option value="">-- Select Brand --</option>
                                        @foreach(App\Models\Brand::orderBy('name')->get() as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="size_id" class="form-label fw-bold">{{ __('Size') }}</label>
                                    <select id="size_id" class="form-control @error('size_id') is-invalid @enderror" name="size_id">
                                        <option value="">-- Select Size --</option>
                                        @foreach(App\Models\Size::orderBy('sort_order')->orderBy('name')->get() as $size)
                                            <option value="{{ $size->id }}" {{ old('size_id') == $size->id ? 'selected' : '' }}>
                                                {{ $size->name }} ({{ $size->value }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('size_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label fw-bold">{{ __('Description') }}</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="base_price" class="form-label fw-bold">{{ __('Base Price') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">R</span>
                                        <input id="base_price" type="number" step="0.01" min="0" class="form-control @error('base_price') is-invalid @enderror" name="base_price" value="{{ old('base_price') }}" required>
                                    </div>
                                    @error('base_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="is_active" class="form-label fw-bold">{{ __('Status') }}</label>
                                    <select id="is_active" class="form-control @error('is_active') is-invalid @enderror" name="is_active">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="categories" class="form-label fw-bold">{{ __('Categories') }}</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        @foreach(App\Models\Category::where('parent_id', null)->orderBy('name')->get() as $category)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="category{{ $category->id }}"
                                                        {{ (is_array(old('categories')) && in_array($category->id, old('categories'))) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="category{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>

                                                @foreach($category->children as $child)
                                                    <div class="form-check ms-4">
                                                        <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $child->id }}" id="category{{ $child->id }}"
                                                            {{ (is_array(old('categories')) && in_array($child->id, old('categories'))) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="category{{ $child->id }}">
                                                            {{ $child->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-list-ul me-2"></i>{{ __('Specifications') }}</span>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-spec">
                                        <i class="fas fa-plus me-1"></i> Add Specification
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="specs-container">
                                    <div class="row align-items-center spec-row mb-3">
                                        <div class="col-md-5">
                                            <select name="specifications[0][id]" class="form-control spec-select">
                                                <option value="">-- Select Specification --</option>
                                                @foreach(App\Models\Specification::orderBy('group')->orderBy('name')->get() as $spec)
                                                    <option value="{{ $spec->id }}">
                                                        {{ $spec->group ? $spec->group . ': ' : '' }}{{ $spec->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="specifications[0][value]" class="form-control" placeholder="Value">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-danger remove-spec" disabled>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add this to your create form, near the end before the submit button -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <i class="fas fa-user-clock me-2"></i>{{ __('Tracking Information') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('Created By') }}</label>
                                            <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                                            <small class="text-muted">{{ Auth::user()->email }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('Creation Date') }}</label>
                                            <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>
                                            <small class="text-muted">Current time</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> {{ __('Create Product') }}
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ms-2">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let specCount = 1;

        // Add specification row
        $('#add-spec').click(function() {
            const newSpecHtml = `
                <div class="row align-items-center spec-row mb-3">
                    <div class="col-md-5">
                        <select name="specifications[${specCount}][id]" class="form-control spec-select">
                            <option value="">-- Select Specification --</option>
                            @foreach(App\Models\Specification::orderBy('group')->orderBy('name')->get() as $spec)
                                <option value="{{ $spec->id }}">
                                    {{ $spec->group ? $spec->group . ': ' : '' }}{{ $spec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="specifications[${specCount}][value]" class="form-control" placeholder="Value">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger remove-spec">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            $('#specs-container').append(newSpecHtml);
            specCount++;
        });

        // Remove specification row
        $(document).on('click', '.remove-spec', function() {
            $(this).closest('.spec-row').remove();
        });
    });
</script>
@endsection
