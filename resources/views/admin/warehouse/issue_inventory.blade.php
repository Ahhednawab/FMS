@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Issue Inventory to Sub-Warehouse</h2>

        <form action="{{ route('warehouses.issue_inventory') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="master_warehouse_id">Master Warehouse</label>
                <select name="master_warehouse_id" id="master_warehouse_id" class="form-control" required>
                    <option value="" disabled selected>Select Master Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        @if($warehouse->type == 'master')
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="sub_warehouse_id">Sub-Warehouse</label>
                <select name="sub_warehouse_id" id="sub_warehouse_id" class="form-control" required>
                    <option value="" disabled selected>Select Sub-Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        @if($warehouse->type == 'sub')
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="inventory_item_id">Inventory Item</label>
                <select name="inventory_item_id" id="inventory_item_id" class="form-control" required>
                    <option value="" disabled selected>Select Inventory Item</option>
                    @foreach($inventoryItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-danger mt-3">Issue Inventory</button>
        </form>
    </div>
@endsection
