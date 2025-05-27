@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Sale Details') }}</span>
                        <div>
                            <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.sales.invoice', $sale) }}" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="fa fa-file-invoice"></i> Invoice
                            </a>
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Client Information</h5>
                            <p><strong>Company:</strong> {{ $sale->client->company_name }}</p>
                            <p><strong>Contact:</strong> {{ $sale->client->contact_person ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $sale->client->phone_number ?? 'N/A' }}</p>
                            <p><strong>Billing Address:</strong> {{ $sale->client->billing_address }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Sale Information</h5>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{
                                    $sale->status == 'Completed' ? 'success' :
                                    ($sale->status == 'Payment Pending' ? 'warning' :
                                    ($sale->status == 'Quote Generated' ? 'info' : 'primary'))
                                }}">
                                    {{ $sale->status }}
                                </span>
                            </p>
                            <p><strong>Created:</strong> {{ $sale->created_at->format('Y-m-d H:i') }}</p>
                            <p><strong>Created By:</strong> {{ $sale->admin->name }}</p>
                            <p><strong>Invoice #:</strong> {{ $sale->invoice_number ?? 'Not Issued' }}</p>
                            @if($sale->invoice_date)
                                <p><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($sale->invoice_date)->format('Y-m-d') }}</p>
                            @endif
                            <p><strong>Markup:</strong> {{ $sale->markup_percentage ?? 0 }}%</p>
                        </div>
                    </div>

                    <h5>Products</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->pivot->quantity }}</td>
                                        <td>R{{ number_format($product->pivot->unit_price, 2) }}</td>
                                        <td>R{{ number_format($product->pivot->quantity * $product->pivot->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th>R{{ number_format($sale->calculateTotal(), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
