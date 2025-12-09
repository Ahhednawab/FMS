@extends('layouts.admin')

@section('content')
<div class="container">

    <h2>Suppliers</h2>

    <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary mb-3">Add Supplier</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Address</th>
                <th width="200px">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->contact }}</td>
                    <td>{{ $supplier->address }}</td>
                    <td>
                 
                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm">Edit</a>

                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this supplier?')" class="btn btn-danger btn-sm">
                                Delete
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $suppliers->links() }}
</div>
@endsection
