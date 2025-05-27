@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-edit me-2"></i>{{ __('Edit Product') }}</span>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Products
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @include('components.flash-messages')

                    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="fade-in">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">{{ __('Product Name') }} <span class="text-danger">*</span></label>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name) }}" required autofocus>
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
                                    <input id="sku" type="text" class="form-control @error('sku') is-invalid @enderror" name="sku" value="{{ old('sku', $product->sku) }}">
                                    <small class="form-text text-muted">Optional unique identifier</small>
                                    @error('sku')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label fw-bold">{{ __('Description') }}</label>
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
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
                                        <input id="base_price" type="number" step="0.01" min="0" class="form-control @error('base_price') is-invalid @enderror" name="base_price" value="{{ old('base_price', $product->base_price) }}" required>
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
                                        <option value="1" {{ old('is_active', $product->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $product->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Add this to your edit form, near the end before the submit button -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <i class="fas fa-user-clock me-2"></i>{{ __('Tracking Information') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('Created By') }}</label>
                                            <input type="text" class="form-control" value="{{ $product->creator ? $product->creator->name : 'Unknown' }}" readonly>
                                            <small class="text-muted">{{ $product->created_at->format('Y-m-d H:i:s') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">{{ __('Last Updated By') }}</label>
                                            <input type="text" class="form-control" value="{{ $product->updater ? $product->updater->name : 'Unknown' }} ({{ Auth::user()->name }} - current)" readonly>
                                            <small class="text-muted">Last: {{ $product->updated_at->format('Y-m-d H:i:s') }} | Current: {{ now()->format('Y-m-d H:i:s') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> {{ __('Update Product') }}
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
