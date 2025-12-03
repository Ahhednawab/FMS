@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Master Warehouse Inventory</h2>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('master_warehouse_inventory.create') }}" class="btn btn-primary mb-3">Add New Inventory</a>

        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Batch Number</th>
                <th>Category</th>
                <th>Expiry Date</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            @foreach($inventory as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->batch_number }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->expiry_date }}</td>
                    <td>{{ $item->price }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
