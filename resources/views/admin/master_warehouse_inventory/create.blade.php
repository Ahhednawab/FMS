@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Add New Inventory Item</h2>

        <form action="{{ route($role_slug . '.master_warehouse_inventory.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="product_id">Product</label>
                <select name="product_id" id="product_id" class="form-control" required>
                    <option value="" disabled selected>Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="batch_number">Batch Number</label>
                <input type="text" name="batch_number" class="form-control" id="batch_number">
            </div>
            {{-- <div class="form-group">
                    <label for="Category">Category</label>
                    <input type="text" name="Category" class="form-control" id="Category">
                </div> --}}

            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" id="expiry_date">
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" class="form-control" id="quantity" required>
            </div>

            <div class="form-group">
                <label for="Price">Price</label>
                <input type="number" name="price" class="form-control" id="Price" required>
            </div>

            <button type="submit" class="btn btn-success mt-3">Add Inventory</button>
        </form>
    </div>
@endsection
