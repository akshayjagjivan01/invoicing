@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Sales Management') }}</span>
                        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> New Sale
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Invoice #</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->id }}</td>
                                        <td>{{ $sale->client->company_name }}</td>
                                        <td>
                                            <span class="badge bg-{{
                                                $sale->status == 'Completed' ? 'success' :
                                                ($sale->status == 'Payment Pending' ? 'warning' :
                                                ($sale->status == 'Quote Generated' ? 'info' : 'primary'))
                                            }}">
                                                {{ $sale->status }}
                                            </span>
                                        </td>
                                        <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $sale->invoice_number ?? 'N/A' }}</td>
                                        <td>R{{ number_format($sale->calculateTotal(), 2) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-info btn-sm">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.sales.invoice', $sale) }}" class="btn btn-secondary btn-sm" target="_blank">
                                                    <i class="fa fa-file-invoice"></i>
                                                </a>
                                                <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sale?');">
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
                                        <td colspan="7" class="text-center">No sales found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $sales->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
