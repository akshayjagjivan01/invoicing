@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Edit Sale') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.sales.update', $sale) }}" id="sale-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="client_id" class="col-md-4 col-form-label text-md-right">{{ __('Client') }}</label>
                            <div class="col-md-6">
                                <select id="client_id" class="form-control @error('client_id') is-invalid @enderror" name="client_id" required>
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $sale->client_id == $client->id ? 'selected' : '' }}>
                                            {{ $client->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-right">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                    <option value="">-- Select Status --</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->name }}" {{ $sale->status == $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="markup_percentage" class="col-md-4 col-form-label text-md-right">{{ __('Markup Percentage (%)') }}</label>
                            <div class="col-md-6">
                                <input id="markup_percentage" type="number" step="0.01" min="0" class="form-control @error('markup_percentage') is-invalid @enderror" name="markup_percentage" value="{{ old('markup_percentage', $sale->markup_percentage) }}">
                                @error('markup_percentage')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="card mt-4 mb-4">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">{{ __('Products') }}</div>
                                    <div class="col-md-6 text-right">
                                        <button type="button" class="btn btn-primary btn-sm" id="add-product-row">
                                            <i class="fa fa-plus"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table" id="products-table">
                                    <thead>
                                        <tr>
                                            <th width="40%">Product</th>
                                            <th width="20%">Quantity</th>
                                            <th width="30%">Base Price</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->products as $index => $product)
                                            <tr class="product-row">
                                                <td>
                                                    <input type="text" name="products[{{ $index }}][name]" class="form-control product-name" required placeholder="Type to search products..." value="{{ $product->name }}">
                                                    <input type="hidden" name="products[{{ $index }}][id]" class="product-id" value="{{ $product->id }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="products[{{ $index }}][quantity]" class="form-control" min="1" value="{{ $product->pivot->quantity }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="products[{{ $index }}][base_price]" step="0.01" min="0" class="form-control product-price" required readonly value="{{ $product->base_price }}">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-product-row" {{ $index === 0 ? 'disabled' : '' }}>
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @if(count($sale->products) === 0)
                                            <tr class="product-row">
                                                <td>
                                                    <input type="text" name="products[0][name]" class="form-control product-name" required placeholder="Type to search products...">
                                                    <input type="hidden" name="products[0][id]" class="product-id">
                                                </td>
                                                <td>
                                                    <input type="number" name="products[0][quantity]" class="form-control" min="1" value="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="products[0][base_price]" step="0.01" min="0" class="form-control product-price" required readonly>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-product-row" disabled>
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update Sale') }}
                                </button>
                                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
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
        let rowCount = {{ count($sale->products) > 0 ? count($sale->products) : 1 }};

        // Add product row
        $('#add-product-row').click(function() {
            const newRow = `
                <tr class="product-row">
                    <td>
                        <input type="text" name="products[${rowCount}][name]" class="form-control product-name" required placeholder="Type to search products...">
                        <input type="hidden" name="products[${rowCount}][id]" class="product-id">
                    </td>
                    <td>
                        <input type="number" name="products[${rowCount}][quantity]" class="form-control" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" name="products[${rowCount}][base_price]" step="0.01" min="0" class="form-control product-price" required readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-product-row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#products-table tbody').append(newRow);
            initializeAutocomplete($('#products-table tbody tr:last-child .product-name'));
            rowCount++;
        });

        // Remove product row
        $(document).on('click', '.remove-product-row', function() {
            $(this).closest('tr').remove();
        });

        // Initialize autocomplete for all product name fields
        initializeAutocomplete($('.product-name'));

        // Function to initialize autocomplete
        function initializeAutocomplete(element) {
            element.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('admin.products.autocomplete') }}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    // Fill the hidden field with product ID
                    $(this).siblings('.product-id').val(ui.item.id);
                    // Fill the price field
                    $(this).closest('tr').find('.product-price').val(ui.item.base_price);
                }
            });
        }

        // Form validation
        $('#sale-form').submit(function(e) {
            let isValid = true;

            // Check if at least one product is selected
            const productRows = $('.product-row').length;
            if (productRows === 0) {
                alert('Please add at least one product.');
                isValid = false;
            }

            // Check if all products have IDs (were selected from autocomplete)
            $('.product-id').each(function() {
                if ($(this).val() === '') {
                    alert('Please select all products from the autocomplete list.');
                    isValid = false;
                    return false;  // Break the loop
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection
