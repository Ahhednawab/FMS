@php
    $maintenance = $vehicleMaintenance;
    $selectedParts = collect($selectedParts ?? []);
@endphp

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="service_date" id="service_date" class="form-control"
                value="{{ old('service_date', optional($maintenance?->service_date)->format('Y-m-d')) }}" required>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Vehicle Number</label>
            <select name="vehicle_id" id="vehicle_id" class="form-control select2" required>
                <option value="">--Select--</option>
                @foreach ($vehicles as $id => $vehicleNo)
                    <option value="{{ $id }}" {{ old('vehicle_id', $maintenance?->vehicle_id) == $id ? 'selected' : '' }}>
                        {{ $vehicleNo }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Vehicle Make</label>
            <input type="text" id="vehicle_make" class="form-control"
                value="{{ old('vehicle_make', $maintenance?->vehicle_make) }}" readonly>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Vehicle Model</label>
            <input type="text" id="vehicle_model" class="form-control"
                value="{{ old('model', $maintenance?->model) }}" readonly>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Mileage</label>
            <input type="text" id="current_mileage" class="form-control"
                value="{{ old('odometer_reading', $maintenance?->odometer_reading) }}" readonly>

            <small id="mileage-message" class="text-danger"></small>

            @error('odometer_reading')
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Maintenance Type</label>
            <select name="maintenance_type" class="form-control" required>
                <option value="">--Select--</option>

                @foreach ($maintenanceTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('maintenance_type', $maintenance?->maintenance_type) == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Work Done</label>
            <input list="work_done_options"
                   name="work_done"
                   id="work_done"
                   class="form-control"
                   value="{{ old('work_done', $maintenance?->workDone?->name) }}"
                   required>

            <datalist id="work_done_options">
                @foreach ($workDones as $workDone)
                    <option value="{{ $workDone }}"></option>
                @endforeach
            </datalist>
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Warehouse</label>
            <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                <option value="">--Select--</option>

                @foreach ($warehouses as $id => $name)
                    <option value="{{ $id }}" {{ old('warehouse_id', $maintenance?->warehouse_id) == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Workshop Row -->
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Workshop</label>
            <select name="workshop_id" class="form-control select2" required>
                <option value="">--Select--</option>

                @foreach ($workshops as $id => $name)
                    <option value="{{ $id }}" {{ old('workshop_id', $maintenance?->workshop_id) == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Remarks Section -->
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ old('remarks', $maintenance?->remarks) }}</textarea>
        </div>
    </div>
</div>

<!-- Divider -->
<hr class="my-4" style="border-top:1px solid #d9d9d9;">

<!-- Cost Section -->
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Labor / Service Charges</label>
            <input type="number"
                   name="labor_cost"
                   id="labor_cost"
                   min="0"
                   step="0.01"
                   class="form-control"
                   value="{{ old('labor_cost', $maintenance?->labor_cost ?? 0) }}">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>Amount</label>
            <input type="number"
                   id="amount"
                   class="form-control"
                   value="{{ old('service_cost', $maintenance?->service_cost ?? 0) }}"
                   readonly>
        </div>
    </div>
</div>



<!-- Parts Table -->
<div class="table-responsive mt-4">
    <table class="table table-bordered" id="parts-table">
        <thead>
            <tr>
                <th>Product</th>
                <th style="width:140px;">Quantity Used</th>
                <th style="width:140px;">Unit Price</th>
                <th style="width:140px;">Total Price</th>
                <th style="width:80px;">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button type="button" class="btn btn-light" id="add-part-row">
        Add Part
    </button>
</div>

<script>
    window.existingMaintenanceParts = @json($selectedParts->values());
</script>