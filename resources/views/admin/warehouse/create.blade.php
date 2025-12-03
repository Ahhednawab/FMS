@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create Warehouse</h2>

        <form action="{{ route('warehouses.create') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Warehouse Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="type">Warehouse Type</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="master">Master Warehouse</option>
                    <option value="sub">Sub Warehouse</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success mt-3">Create Warehouse</button>
        </form>
    </div>
@endsection
