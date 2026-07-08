@extends('layouts.admin')

@section('content')
    <div class="container pt-5">
        <h2>Add New Inventory Item</h2>

        {{-- Master Warehouse Info Banner --}}
        @if ($masterWarehouse)
            <div class="alert alert-success d-flex align-items-center" role="alert"
                style="border-left: 4px solid #28a745;">
                <i class="icon-shield-check mr-3" style="font-size:1.3rem;"></i>
                <div>
                    <strong>Receiving Warehouse:</strong>
                    <span class="badge badge-success ml-1" style="font-size:0.85rem;">{{ $masterWarehouse->name }}</span>
                    <small class="text-muted ml-2">(Serial: {{ $masterWarehouse->serial_no }})</small>
                    <br>
                    <small class="text-muted">All inventory additions are automatically received by the Master Warehouse first.</small>
                </div>
            </div>
        @else
            <div class="alert alert-danger d-flex align-items-center justify-content-between" role="alert"
                style="border-left: 4px solid #dc3545;">
                <div>
                    <i class="icon-warning2 mr-2"></i>
                    <strong>No Master Warehouse Configured!</strong>
                    <span class="ml-1 text-muted small">You must set a Master Warehouse before adding inventory.</span>
                </div>
                <a href="{{ route('warehouses.index') }}" class="btn btn-sm btn-danger">
                    Go to Warehouses
                </a>
            </div>
        @endif

        {{-- Error from store() if master not set --}}
        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route($role_slug . '.master_warehouse_inventory.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" class="form-control" required>
                            <option value="" disabled selected>Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="batch_number">Batch Number</label>
                        <input type="text" name="batch_number" class="form-control" id="batch_number"
                            value="{{ old('batch_number') }}" placeholder="Auto-generated if left blank">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" class="form-control" id="quantity" required min="1"
                            value="{{ old('quantity') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="Price">Unit Price</label>
                        <input type="number" name="price" class="form-control" id="Price" required step="0.01"
                            min="0" value="{{ old('price') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" id="expiry_date"
                            value="{{ old('expiry_date') }}">
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success" {{ !$masterWarehouse ? 'disabled' : '' }}>
                    <i class="icon-plus3 mr-1"></i> Add Inventory to Master Warehouse
                </button>
                <a href="{{ route('master_warehouse_inventory.index') }}" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#product_id').select2({
                placeholder: 'Select Product',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush
