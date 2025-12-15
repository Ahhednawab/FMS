@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Purchases</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary mb-3">Add New Purchase</a>

        <table class="table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->quantity }}</td>
                        <td>{{ number_format($purchase->price, 2) }}</td>
                        <td>{{ number_format($purchase->price * $purchase->quantity, 2) }}</td>
                        <td>{{ $purchase->purchase_date }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4">
                        <div class="float-right">
                            {{ $purchases->links() }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
