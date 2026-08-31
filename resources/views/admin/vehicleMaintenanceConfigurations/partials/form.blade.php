@php
    $config = $configuration ?? null;
    $isEdit = (bool) $config;
    $makes = $makes ?? collect();
    $modelYears = $modelYears ?? collect();
    $currentMake = old('make', $config?->make);
    // Model is a multi-select on create, so old input comes back as an array.
    $selectedModels = collect((array) old('model', $config ? [$config->model] : []))
        ->map(fn ($value) => (string) $value);
@endphp

{{--
    On Create, Make and Model are searchable dropdowns. On Edit they are
    read-only: they identify the configuration and are the key the predictive
    engine matches vehicles on, so only the intervals below can be changed.
--}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Vehicle Make @unless ($isEdit)<span class="text-danger">*</span>@endunless</label>

            @if ($isEdit)
                <input type="text" class="form-control" value="{{ $config->make }}" readonly>
            @else
                <select name="make" id="config_make" class="form-control select2-tags"
                    data-placeholder="Select or type a Make">
                    <option value="">--Select--</option>
                    @foreach ($makes as $make)
                        <option value="{{ $make }}" {{ $currentMake == $make ? 'selected' : '' }}>{{ $make }}</option>
                    @endforeach
                    @if ($currentMake && !$makes->contains($currentMake))
                        <option value="{{ $currentMake }}" selected>{{ $currentMake }}</option>
                    @endif
                </select>
            @endif

            @error('make')
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="col-md-8">
        <div class="form-group">
            <label>Vehicle Model @unless ($isEdit)<span class="text-danger">*</span>@endunless</label>

            @if ($isEdit)
                <input type="text" class="form-control" value="{{ $config->model }}" readonly>
            @else
                <select name="model[]" id="config_model" class="form-control select2-multi" multiple
                    data-placeholder="Select one or more model years">
                    @foreach ($modelYears as $year)
                        <option value="{{ $year }}" {{ $selectedModels->contains((string) $year) ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">
                    Pick every model year these intervals apply to — a configuration is saved for each year.
                </small>
            @endif

            @error('model')
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror
            @error('model.*')
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
