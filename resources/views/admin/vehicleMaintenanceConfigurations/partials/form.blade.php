@php
    $config = $configuration ?? null;
    $currentMake = old('make', $config?->make);
    $currentModel = old('model', $config?->model);
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Vehicle Make <span class="text-danger">*</span></label>
            <select name="make" id="config_make" class="form-control select2-tags" data-placeholder="Select or type a Make">
                <option value="">--Select--</option>
                @foreach ($makes as $make)
                    <option value="{{ $make }}" {{ $currentMake == $make ? 'selected' : '' }}>{{ $make }}</option>
                @endforeach
                @if ($currentMake && !$makes->contains($currentMake))
                    <option value="{{ $currentMake }}" selected>{{ $currentMake }}</option>
                @endif
            </select>
            @error('make')
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Vehicle Model <span class="text-danger">*</span></label>
            <select name="model" id="config_model" class="form-control select2-tags" data-placeholder="Select or type a Model">
                <option value="">--Select--</option>
                @foreach ($models as $model)
                    <option value="{{ $model }}" {{ $currentModel == $model ? 'selected' : '' }}>{{ $model }}</option>
                @endforeach
                @if ($currentModel && !$models->contains($currentModel))
                    <option value="{{ $currentModel }}" selected>{{ $currentModel }}</option>
                @endif
            </select>
            @error('model')
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>

<hr class="my-3" style="border-top:1px solid #d9d9d9;">

<p class="text-muted mb-3">
    Enter the maintenance interval (in kilometers) for each predefined item.
    Leave a field blank to exclude that item from predictive monitoring for this Make/Model.
</p>

<div class="row">
    @foreach ($items as $name => $column)
        <div class="col-md-3">
            <div class="form-group">
                <label>{{ $name }} <small class="text-muted">(KM)</small></label>
                <input type="number" min="0" step="1" name="{{ $column }}" class="form-control"
                    value="{{ old($column, $config?->{$column}) }}" placeholder="e.g. 5000">
                @error($column)
                    <small class="text-danger d-block">{{ $message }}</small>
                @enderror
            </div>
        </div>
    @endforeach
</div>
