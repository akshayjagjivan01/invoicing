@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Client Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Your Sales</h5>
                                    <p class="card-text display-4">{{ Auth::user()->client->sales->count() }}</p>
                                    <a href="{{ route('client.sales.index') }}" class="btn btn-light">View All Sales</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">Pending Payments</h5>
                                    <p class="card-text display-4">
                                        {{ Auth::user()->client->sales->where('status', 'Payment Pending')->count() }}
                                    </p>
                                    <a href="{{ route('client.sales.index', ['status' => 'Payment Pending']) }}" class="btn btn-dark">View Pending</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">Recent Sales</div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(Auth::user()->client->sales()->orderBy('created_at', 'desc')->take(5)->get() as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_number ?? 'N/A' }}</td>
                                            <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $sale->status == 'Completed' ? 'success' : ($sale->status == 'Payment Pending' ? 'warning' : 'primary') }}">
                                                    {{ $sale->status }}
                                                </span>
                                            </td>
                                            <td>R{{ number_format($sale->calculateTotal(), 2) }}</td>
                                            <td>
                                                <a href="{{ route('client.sales.show', $sale) }}" class="btn btn-sm btn-info">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
