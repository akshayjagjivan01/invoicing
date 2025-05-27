@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Invoice') }} {{ $sale->invoice_number ?? 'Draft' }}</span>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-sm ms-2">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body invoice-container">
                    <div class="invoice-header">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <img src="{{ asset('images/logo.png') }}" height="60" alt="IT Blueprint Solutions">
                                <h2 class="mt-3">IT Blueprint Solutions</h2>
                                <p class="invoice-details">
                                    123 Technology Ave<br>
                                    Johannesburg, 2000<br>
                                    South Africa<br>
                                    Phone: +27 11 123 4567<br>
                                    Email: info@itblueprintsolutions.com
                                </p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h1 class="invoice-title">INVOICE</h1>
                                <h3 class="text-primary">{{ $sale->invoice_number ?? 'Draft' }}</h3>
                                <p class="invoice-details">
                                    Date: {{ $sale->invoice_date ? \Carbon\Carbon::parse($sale->invoice_date)->format('Y-m-d') : date('Y-m-d') }}<br>
                                    Status: <span class="badge bg-{{ $sale->status == 'Completed' ? 'success' : ($sale->status == 'Payment Pending' ? 'warning' : 'primary') }}">
                                        {{ $sale->status }}
                                    </span><br>
                                    Sale ID: #{{ $sale->id }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-primary">Bill To:</h5>
                                <address>
                                    <strong>{{ $sale->client->company_name }}</strong><br>
                                    {{ $sale->client->billing_address }}<br>
                                    @if($sale->client->contact_person)
                                        Contact: {{ $sale->client->contact_person }}<br>
                                    @endif
                                    @if($sale->client->phone_number)
                                        Phone: {{ $sale->client->phone_number }}
                                    @endif
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-primary">Ship To:</h5>
                                <address>
                                    <strong>{{ $sale->client->company_name }}</strong><br>
                                    {{ $sale->client->shipping_address ?? $sale->client->billing_address }}
                                </address>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Description</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->description ?? '-' }}</td>
                                        <td class="text-center">{{ $product->pivot->quantity }}</td>
                                        <td class="text-end">R{{ number_format($product->pivot->unit_price, 2) }}</td>
                                        <td class="text-end">R{{ number_format($product->pivot->quantity * $product->pivot->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Subtotal:</th>
                                    <th class="text-end">R{{ number_format($total, 2) }}</th>
                                </tr>
                                @if($sale->markup_percentage)
                                    <tr>
                                        <th colspan="4" class="text-end">Markup ({{ $sale->markup_percentage }}%):</th>
                                        <th class="text-end">Included</th>
                                    </tr>
                                @endif
                                <tr class="table-active">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-end fs-5">R{{ number_format($total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row mt-5">
                        <div class="col-md-7">
                            <div class="p-4 border rounded">
                                <h5 class="text-primary">Payment Terms:</h5>
                                <p>Payment due within 30 days of invoice date.</p>
                                <p>Please make payment to:</p>
                                <div class="d-flex">
                                    <i class="fas fa-university mt-1 me-2 text-primary"></i>
                                    <div>
                                        <strong>Bank:</strong> Example Bank<br>
                                        <strong>Account Name:</strong> IT Blueprint Solutions<br>
                                        <strong>Account Number:</strong> 123456789<br>
                                        <strong>Branch Code:</strong> 12345<br>
                                        <strong>Reference:</strong> {{ $sale->invoice_number ?? 'Sale ID ' . $sale->id }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-4 bg-light rounded text-center">
                                <h5 class="text-primary">Thank You for Your Business!</h5>
                                <p class="mb-2">For any inquiries regarding this invoice:</p>
                                <p class="mb-0">
                                    <i class="fas fa-envelope me-1"></i> accounts@itblueprintsolutions.com<br>
                                    <i class="fas fa-phone me-1"></i> +27 11 123 4567
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <hr>
                            <p class="mb-0 text-muted">
                                <small>&copy; {{ date('Y') }} IT Blueprint Solutions. All rights reserved.</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style type="text/css">
    /* General invoice styling */
    .invoice-container {
        padding: 20px;
    }

    /* Print-specific styles */
    @media print {
        /* Reset and preparation */
        @page {
            size: A4;
            margin: 0.5cm;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: white;
            font-size: 14px;
        }

        /* Hide unnecessary elements */
        header, footer, aside, nav,
        .no-print, .btn, .card-header,
        form, .alert, .navbar {
            display: none !important;
        }

        /* Main container adjustments */
        .container, .container-fluid, .card, .card-body {
            width: 100%;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* Keep invoice content visible and properly formatted */
        .invoice-container {
            display: block !important;
            position: relative !important;
            width: 100% !important;
            padding: 5mm !important;
            margin: 0 !important;
            overflow: visible !important;
        }

        /* Table adjustments */
        .table, .table-responsive {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        /* Ensure table header prints on each page */
        thead {
            display: table-header-group;
        }

        /* Avoid breaking inside rows */
        tr {
            page-break-inside: avoid;
        }

        /* Background colors print properly */
        .bg-light, .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Make text content visible */
        .text-primary {
            color: #007bff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Badge colors */
        .badge.bg-success {
            background-color: #28a745 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: black !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge.bg-primary {
            background-color: #007bff !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Proper text alignment in printed view */
        .text-end {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }
    }
</style>
@endsection
