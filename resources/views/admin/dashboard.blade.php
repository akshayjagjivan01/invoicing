@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center fade-in">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Admin Dashboard') }}</span>
                    <span class="badge bg-primary">{{ date('l, F j, Y') }}</span>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title">Clients</h5>
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                    <p class="display-4">{{ \App\Models\Client::count() }}</p>
                                    <a href="{{ route('admin.clients.index') }}" class="btn btn-light">
                                        <i class="fas fa-arrow-right"></i> Manage Clients
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title">Products</h5>
                                        <i class="fas fa-box fa-2x"></i>
                                    </div>
                                    <p class="display-4">{{ \App\Models\Product::count() }}</p>
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-light">
                                        <i class="fas fa-arrow-right"></i> Manage Products
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title">Sales</h5>
                                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                                    </div>
                                    <p class="display-4">{{ \App\Models\Sale::count() }}</p>
                                    <a href="{{ route('admin.sales.index') }}" class="btn btn-light">
                                        <i class="fas fa-arrow-right"></i> Manage Sales
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-list me-2"></i>Recent Sales</span>
                                    <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Client</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(\App\Models\Sale::with('client')->orderBy('created_at', 'desc')->take(5)->get() as $sale)
                                                    <tr>
                                                        <td>{{ $sale->client->company_name }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $sale->status == 'Completed' ? 'success' : ($sale->status == 'Payment Pending' ? 'warning' : 'primary') }}">
                                                                {{ $sale->status }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                @if(\App\Models\Sale::count() == 0)
                                                    <tr>
                                                        <td colspan="4" class="text-center">No sales records found.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-2"></i>Status Summary
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Get status data for chart
    $.ajax({
        url: "{{ route('admin.sales.status-summary') }}",
        method: 'GET',
        success: function(data) {
            const ctx = document.getElementById('statusChart').getContext('2d');

            // Define nicer colors for the chart
            const chartColors = [
                '#4a6cf7',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6',
                '#ec4899',
                '#06b6d4'
            ];

            const statusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: '# of Sales',
                        data: Object.values(data),
                        backgroundColor: chartColors.slice(0, Object.keys(data).length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'Sales by Status',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            });
        },
        error: function() {
            // Handle the case when there's no data
            const ctx = document.getElementById('statusChart').getContext('2d');
            ctx.font = '16px Arial';
            ctx.fillStyle = '#6b7280';
            ctx.textAlign = 'center';
            ctx.fillText('No status data available', ctx.canvas.width/2, ctx.canvas.height/2);
        }
    });
});
</script>
@endsection
