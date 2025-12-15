@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Supplier Details</h2>

        <p><strong>Name:</strong> {{ $supplier->name }}</p>
        <p><strong>Contact:</strong> {{ $supplier->contact }}</p>
        <p><strong>Address:</strong> {{ $supplier->address }}</p>

        <a href="{{ route($role_slug . '.suppliers.index') }}" class="btn btn-secondary">Back</a>
    </div>
@endsection
