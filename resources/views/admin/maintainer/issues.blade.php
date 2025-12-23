@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Vehicle Maintenance Issue</h4>
            </div>

            <div class="card-body">
                <form method="POST" action="#">
                    @csrf

                    {{-- Issue Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label">Issue</label>
                        <select name="issue_id" class="form-control" required>
                            <option value="">Select Issue</option>
                            @foreach ($issues as $issue)
                                <option value="{{ $issue->id }}">
                                    {{ $issue->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vehicle Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="form-control" required>
                            <option value="">Select Vehicle</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->vehicle_no ?? 'Vehicle #' . $vehicle->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Inventory Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label">Inventory Item</label>
                        <div class="d-flex gap-2">
                            <select id="inventorySelect" class="form-control mr-2">
                                <option value="">Select Inventory</option>
                                @foreach ($inventory as $item)
                                    <option value="{{ $item->product->name ?? '' }}">
                                        {{ $item->product->name ?? 'Item #' . $item->id }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" class="btn btn-primary" id="addInventory">
                                Add
                            </button>
                        </div>
                    </div>

                    {{-- Added Inventory Fields --}}
                    <div id="inventoryContainer"></div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-success">
                        Submit
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.getElementById('addInventory').addEventListener('click', function() {
            let select = document.getElementById('inventorySelect');
            let value = select.value;

            if (!value) return;

            let container = document.getElementById('inventoryContainer');

            let div = document.createElement('div');
            div.classList.add('mb-2');

            div.innerHTML = `
            <input type="text" class="form-control" value="${value}" disabled>
            <input type="hidden" name="inventory_items[]" value="${value}">
        `;

            container.appendChild(div);
            select.value = '';
        });
    </script>
@endpush
