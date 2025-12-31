@extends('layouts.admin')

@section('content')
    <div class="container pt-5">
        <h2>Add New Inventory Item</h2>

        <form action="{{ route($role_slug . '.master_warehouse_inventory.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" class="form-control" required>
                            <option value="" disabled selected>Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="batch_number">Batch Number</label>
                        <input type="text" name="batch_number" class="form-control" id="batch_number">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" class="form-control" id="quantity" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="Price">Unit Price</label>
                        <input type="number" name="price" class="form-control" id="Price" required>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" id="expiry_date">
                    </div>

                </div>
            </div>


            {{-- <div class="form-group">
                    <label for="Category">Category</label>
                    <input type="text" name="Category" class="form-control" id="Category">
                </div> --}}





            <button type="submit" class="btn btn-success mt-3">Add Inventory</button>
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
