@extends('layouts.admin')

@section('content')
    <div class="container">
        <h2>Add Supplier</h2>

        @include('admin.suppliers.form', [
            'route' => route('suppliers.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
