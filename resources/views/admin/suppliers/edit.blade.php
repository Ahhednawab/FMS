@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Edit Supplier</h2>

        @include('admin.suppliers.form', [
            'route' => route($role_slug . '.suppliers.update', $supplier),
            'method' => 'PUT',
            'supplier' => $supplier,
        ])
    </div>
@endsection
