@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('Create New Sale') }}</span>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.sales.store') }}" id="sale-form">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="client_id" class="form-label fw-bold">{{ __('Client') }} <span class="text-danger">*</span></label>
                                    <select id="client_id" class="form-control @error('client_id') is-invalid @enderror" name="client_id" required>
                                        <option value="">-- Select Client --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label fw-bold">{{ __('Status') }} <span class="text-danger">*</span></label>
                                    <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                        <option value="">-- Select Status --</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->name }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="markup_percentage" class="form-label fw-bold">{{ __('Markup Percentage (%)') }}</label>
                            <div class="input-group">
                                <input id="markup_percentage" type="number" step="0.01" min="0" class="form-control @error('markup_percentage') is-invalid @enderror" name="markup_percentage" value="{{ old('markup_percentage') }}">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('markup_percentage')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Leave empty for no markup</small>
                        </div>

                        <div class="card mt-4 mb-4">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-box me-2"></i>{{ __('Products') }}</span>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-product-row">
                                        <i class="fas fa-plus me-1"></i> Add Product
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="products-table">
                                        <thead>
                                            <tr>
                                                <th width="40%">Product <span class="text-danger">*</span></th>
                                                <th width="20%">Quantity <span class="text-danger">*</span></th>
                                                <th width="30%">Base Price</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="product-row">
                                                <td>
                                                    <input type="text" name="products[0][name]" class="form-control product-name" required placeholder="Type to search products...">
                                                    <input type="hidden" name="products[0][id]" class="product-id">
                                                </td>
                                                <td>
                                                    <input type="number" name="products[0][quantity]" class="form-control" min="1" value="1" required>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">R</span>
                                                        <input type="number" name="products[0][base_price]" step="0.01" min="0" class="form-control product-price" required readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-product-row" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="form-group d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> {{ __('Save Sale') }}
                            </button>
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary ms-2">
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
        let rowCount = 1;

        // Add product row
        $('#add-product-row').click(function() {
            const newRow = `
                <tr class="product-row fade-in">
                    <td>
                        <input type="text" name="products[${rowCount}][name]" class="form-control product-name" required placeholder="Type to search products...">
                        <input type="hidden" name="products[${rowCount}][id]" class="product-id">
                    </td>
                    <td>
                        <input type="number" name="products[${rowCount}][quantity]" class="form-control" min="1" value="1" required>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">R</span>
                            <input type="number" name="products[${rowCount}][base_price]" step="0.01" min="0" class="form-control product-price" required readonly>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-product-row">
                            <i class="fas fa-trash"></i>
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
            $(this).closest('tr').fadeOut('fast', function() {
                $(this).remove();
            });
        });

        // Initialize autocomplete for the first row
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
                Swal.fire({
                    icon: 'error',
                    title: 'No Products',
                    text: 'Please add at least one product.',
                });
                isValid = false;
            }

            // Check if all products have IDs (were selected from autocomplete)
            $('.product-id').each(function() {
                if ($(this).val() === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Selection',
                        text: 'Please select all products from the autocomplete list.',
                    });
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
