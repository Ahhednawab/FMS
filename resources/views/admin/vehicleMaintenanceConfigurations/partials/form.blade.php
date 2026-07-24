@php
    $config = $configuration ?? null;
@endphp

{{--
    Vehicle Make and Model identify the configuration and are the key the
    predictive engine matches vehicles on, so they are read-only here.
    Only the maintenance intervals below can be updated.
--}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Vehicle Make</label>
            <input type="text" class="form-control" value="{{ $config?->make }}" readonly>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Vehicle Model</label>
            <input type="text" class="form-control" value="{{ $config?->model }}" readonly>
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
