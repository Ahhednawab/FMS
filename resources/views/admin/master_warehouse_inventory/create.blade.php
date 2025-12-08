@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Add New Inventory Item</h2>

        <form action="{{ route('admin.master_warehouse_inventory.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text" name="name" class="form-control" id="name" required>
            </div>

            <div class="form-group">
                <label for="batch_number">Batch Number</label>
                <input type="text" name="batch_number" class="form-control" id="batch_number">
            </div>
            <div class="form-group">
                <label for="Category">Category</label>
                <input type="text" name="Category" class="form-control" id="Category">
            </div>

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
                <input type="number" name="Price" class="form-control" id="Price" required>
            </div>

            <button type="submit" class="btn btn-success mt-3">Add Inventory</button>
        </form>
    </div>
@endsection
