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
            @php
                $selectedWorkDones = collect(old('work_done', $maintenance?->workDoneNames() ?? []))
                    ->map(fn ($name) => trim((string) $name))
                    ->filter()
                    ->values();
            @endphp
            <label>Work Done</label>
            <select name="work_done[]" id="work_done" class="form-control" multiple required
                data-placeholder="Search or type to add new">
                @foreach ($workDones as $workDoneId => $workDoneName)
                    <option value="{{ $workDoneName }}" data-id="{{ $workDoneId }}"
                        {{ $selectedWorkDones->contains($workDoneName) ? 'selected' : '' }}>
                        {{ $workDoneName }}
                    </option>
                @endforeach
                @foreach ($selectedWorkDones->reject(fn ($name) => collect($workDones)->contains($name)) as $missingName)
                    <option value="{{ $missingName }}" selected>{{ $missingName }}</option>
                @endforeach
            </select>
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

<!-- Alert Integration Row -->
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Alert <small class="text-muted">(Optional)</small></label>
            <select name="alert_id" id="alert_id" class="form-control select2">
                <option value="">--Select Alert--</option>
                @foreach ($alerts as $alert)
                    <option value="{{ $alert->id }}"
                        data-threshold="{{ $alert->threshold }}"
                        {{ old('alert_id', $maintenance?->alert_id) == $alert->id ? 'selected' : '' }}>
                        {{ $alert->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Threshold (KM) <small class="text-muted">(Auto-filled)</small></label>
            <input type="number" name="threshold_km" id="threshold_km" class="form-control"
                value="{{ old('threshold_km', $maintenance?->threshold_km) }}"
                placeholder="Auto-populated from alert" readonly>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Alert Before (KM)</label>
            <input type="number" name="alert_before_km" id="alert_before_km" class="form-control"
                value="{{ old('alert_before_km', $maintenance?->alert_before_km) }}"
                min="0" placeholder="e.g. 500">
            <small class="text-muted">Trigger alert this many KM before the threshold.</small>
        </div>
    </div>
</div>

<!-- Computed Alert Mileages Row -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Next Due Mileage (KM) <small class="text-muted">(Auto-calculated)</small></label>
            <input type="number" name="next_due_mileage" id="next_due_mileage" class="form-control"
                value="{{ old('next_due_mileage', $maintenance?->next_due_mileage) }}" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Alert Start Mileage (KM) <small class="text-muted">(Auto-calculated)</small></label>
            <input type="number" name="alert_start_mileage" id="alert_start_mileage" class="form-control"
                value="{{ old('alert_start_mileage', $maintenance?->alert_start_mileage) }}" readonly>
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