@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Create Quick Sale</span>
                    <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary">Back to Sales</a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.quicksales.store') }}">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Client Information</h4>
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="new_client" id="new_client">
                                    <label class="form-check-label" for="new_client">
                                        Create New Client
                                    </label>
                                </div>

                                <div id="existing_client_section">
                                    <div class="form-group">
                                        <label for="client_id">Select Client</label>
                                        <select class="form-control" id="client_id" name="client_id">
                                            <option value="">Select a client</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div id="new_client_section" style="display: none;">
                                    <div class="form-group">
                                        <label for="new_client_name">Client Name</label>
                                        <input type="text" class="form-control" id="new_client_name" name="new_client_name">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_client_email">Email</label>
                                        <input type="email" class="form-control" id="new_client_email" name="new_client_email">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_client_phone">Phone</label>
                                        <input type="text" class="form-control" id="new_client_phone" name="new_client_phone">
                                    </div>
                                    <div class="form-group">
                                        <label for="new_client_address">Address</label>
                                        <textarea class="form-control" id="new_client_address" name="new_client_address" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale_date">Sale Date</label>
                                    <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4>Products</h4>
                                <hr>
                                <div id="products-container">
                                    <div class="product-row card mb-3 p-3">
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input new-product-check" type="checkbox" name="products[0][new_product]">
                                                    <label class="form-check-label">
                                                        Add New Product
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-5 existing-product-section">
                                                <div class="form-group">
                                                    <label>Select Product</label>
                                                    <select class="form-control product-select" name="products[0][id]">
                                                        <option value="">Select a product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-5 new-product-section" style="display: none;">
                                                <div class="form-group">
                                                    <label>Product Name</label>
                                                    <input type="text" class="form-control" name="products[0][new_product_name]">
                                                </div>
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <input type="text" class="form-control" name="products[0][new_product_description]">
                                                </div>
                                                <div class="form-group">
                                                    <label>Price</label>
                                                    <input type="number" step="0.01" class="form-control new-product-price" name="products[0][new_product_price]">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Quantity</label>
                                                    <input type="number" min="1" class="form-control quantity" name="products[0][quantity]" value="1">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Price</label>
                                                    <input type="number" step="0.01" class="form-control price" name="products[0][price]" readonly>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Subtotal</label>
                                                    <input type="number" step="0.01" class="form-control subtotal" readonly>
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger remove-product mt-4">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="add-product" class="btn btn-secondary">Add Another Product</button>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12 text-right">
                                <h4>Total: R<span id="grand-total">0.00</span></h4>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Create Sale</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Client section toggle
        $('#new_client').change(function() {
            if($(this).is(':checked')) {
                $('#existing_client_section').hide();
                $('#new_client_section').show();
            } else {
                $('#existing_client_section').show();
                $('#new_client_section').hide();
            }
        });

        // Product row functions
        let productIndex = 0;

        // Add product row
        $('#add-product').click(function() {
            productIndex++;
            const newRow = $('.product-row').first().clone();

            // Update names and IDs
            newRow.find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace('[0]', '[' + productIndex + ']'));
                }
            });

            // Clear values
            newRow.find('select').val('');
            newRow.find('input[type="text"], input[type="number"]').val('');
            newRow.find('input[type="checkbox"]').prop('checked', false);
            newRow.find('.new-product-section').hide();
            newRow.find('.existing-product-section').show();
            newRow.find('.quantity').val(1);

            $('#products-container').append(newRow);
            bindEvents();
        });

        // Handle product selection change
        function bindEvents() {
            // Remove product
            $('.remove-product').off('click').on('click', function() {
                if ($('.product-row').length > 1) {
                    $(this).closest('.product-row').remove();
                    calculateTotal();
                }
            });

            // New product toggle
            $('.new-product-check').off('change').on('change', function() {
                const row = $(this).closest('.product-row');
                if($(this).is(':checked')) {
                    row.find('.existing-product-section').hide();
                    row.find('.new-product-section').show();
                } else {
                    row.find('.existing-product-section').show();
                    row.find('.new-product-section').hide();
                }
            });

            // Product selection
            $('.product-select').off('change').on('change', function() {
                const row = $(this).closest('.product-row');
                const selectedOption = $(this).find('option:selected');
                const price = selectedOption.data('price');

                if(price) {
                    row.find('.price').val(price);
                    updateSubtotal(row);
                }
            });

            // New product price change
            $('.new-product-price').off('input').on('input', function() {
                const row = $(this).closest('.product-row');
                row.find('.price').val($(this).val());
                updateSubtotal(row);
            });

            // Quantity change
            $('.quantity').off('input').on('input', function() {
                updateSubtotal($(this).closest('.product-row'));
            });
        }

        function updateSubtotal(row) {
            const price = parseFloat(row.find('.price').val()) || 0;
            const quantity = parseInt(row.find('.quantity').val()) || 0;
            const subtotal = price * quantity;

            row.find('.subtotal').val(subtotal.toFixed(2));
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            $('.subtotal').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#grand-total').text(total.toFixed(2));
        }

        // Initial binding
        bindEvents();
    });
</script>
@endpush
@endsection
