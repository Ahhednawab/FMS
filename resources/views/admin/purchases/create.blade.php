@extends('layouts.admin')



@section('content')
    {{-- <div class="container mt-2">
        @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif --}}
        <h2>Add New Purchase</h2>

        <form action="{{ route('admin.purchases.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="supplier_id">Supplier</label>
                <select name="supplier_id" id="supplier_id" class="form-control" required>
                    <option value="" disabled selected>Select Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" name="item_name" id="item_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input 
                    type="number" 
                    name="price" 
                    id="price" 
                    class="form-control" 
                    step="0.01" 
                    min="0" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date</label>
                <input type="date" name="purchase_date" id="purchase_date" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success mt-3">Add Purchase</button>
        </form>
    </div>
@endsection
