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
            <select name="maintenance_type" id="maintenance_type" class="form-control" required>
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
            <small id="work-done-hint" class="text-muted d-block mt-1"></small>
        </div>
    </div>

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

<!-- Predictive Maintenance Preview (auto-calculated from configuration) -->
<div class="row" id="predictive-preview-row" style="display:none;">
    <div class="col-md-12">
        <div class="alert alert-info">
            <strong><i class="icon-calculator mr-1"></i> Predictive Maintenance Schedule</strong>
            <div id="predictive-preview-body" class="mt-2"></div>
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



<!-- Products / Parts Section -->
<hr class="my-4" style="border-top:1px solid #d9d9d9;">

<h6 class="font-weight-semibold mb-3">Products Used</h6>

<p class="text-muted mb-3">
    Each row draws from its own warehouse — pick a warehouse first and the
    product list will show only what that warehouse has in stock.
</p>

<!-- Parts Table: warehouse + product are chosen per row -->
<div class="table-responsive">
    <table class="table table-bordered" id="parts-table">
        <thead>
            <tr>
                <th style="min-width:200px;">Warehouse</th>
                <th style="min-width:220px;">Product</th>
                <th style="width:140px;">Quantity Used</th>
                <th style="width:110px;">Unit</th>
                <th style="width:140px;">Unit Price</th>
                <th style="width:140px;">Total Price</th>
                <th style="width:90px;">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button type="button" class="btn btn-light" id="add-part-row">
        Add Product
    </button>
</div>

<script>
    window.existingMaintenanceParts = @json($selectedParts->values());

    // Warehouse options for the per-row Warehouse dropdown in the parts table.
    window.warehouseOptions = @json(collect($warehouses)->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values());

    // Predefined predictive maintenance items (shown as Work Done options when
    // Maintenance Type = Predictive). Custom entries are treated as one-time.
    window.predictiveItems = @json(array_values($predictiveItems ?? []));

    // The free-text Work Done catalog (id + name) used for non-predictive types.
    window.workDoneCatalog = @json(collect($workDones)->map(fn ($name, $id) => ['id' => $id, 'name' => $name])->values());
</script>