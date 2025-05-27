@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-box me-2"></i>{{ __('Product Details') }}</span>
                        <div>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @include('components.flash-messages')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    Basic Information
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th width="30%">ID:</th>
                                                <td>{{ $product->id }}</td>
                                            </tr>
                                            <tr>
                                                <th>Name:</th>
                                                <td>{{ $product->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>SKU:</th>
                                                <td>{{ $product->sku ?? 'Not specified' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Base Price:</th>
                                                <td>R{{ number_format($product->base_price, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status:</th>
                                                <td>
                                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Created:</th>
                                                <td>{{ $product->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Last Updated:</th>
                                                <td>{{ $product->updated_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    Description
                                </div>
                                <div class="card-body">
                                    @if($product->description)
                                        <p>{{ $product->description }}</p>
                                    @else
                                        <p class="text-muted">No description provided.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header bg-light">
                                    Usage in Sales
                                </div>
                                <div class="card-body">
                                    <p><strong>Used in {{ $product->sales->count() }} sales</strong></p>
                                    @if($product->sales->count() > 0)
                                        <div class="list-group">
                                            @foreach($product->sales->take(5) as $sale)
                                                <a href="{{ route('admin.sales.show', $sale->id) }}" class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">Sale #{{ $sale->id }}</h6>
                                                        <span>{{ $sale->created_at->format('Y-m-d') }}</span>
                                                    </div>
                                                    <p class="mb-1">{{ $sale->client->company_name }}</p>
                                                    <small>Qty: {{ $sale->pivot->quantity }} × R{{ number_format($sale->pivot->unit_price, 2) }}</small>
                                                </a>
                                            @endforeach
                                        </div>
                                        @if($product->sales->count() > 5)
                                            <div class="text-center mt-3">
                                                <p class="text-muted">Showing 5 of {{ $product->sales->count() }} sales.</p>
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-muted">This product has not been used in any sales yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
<!-- Add this to your product details view -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <i class="fas fa-user-clock me-2"></i>{{ __('Audit Information') }}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('Created By') }}:</label>
                    <p>
                        {{ $product->creator ? $product->creator->name : 'Unknown' }}
                        <br>
                        <small class="text-muted">{{ $product->created_at->format('Y-m-d H:i:s') }}</small>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('Last Updated By') }}:</label>
                    <p>
                        {{ $product->updater ? $product->updater->name : 'Unknown' }}
                        <br>
                        <small class="text-muted">{{ $product->updated_at->format('Y-m-d H:i:s') }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
                    <div class="mt-4 text-end">
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" data-confirm="Are you sure you want to delete this product?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash me-1"></i> Delete Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
