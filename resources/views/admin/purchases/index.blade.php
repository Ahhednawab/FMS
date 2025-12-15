@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Purchases</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route($role_slug . '.purchases.create') }}" class="btn btn-primary mb-3">Add New Purchase</a>

        <table class="table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->quantity }}</td>
                        <td>{{ $purchase->price }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
