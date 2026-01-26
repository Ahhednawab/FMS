@extends('layouts.admin')

@section('content')
    <div class="container">
        <h3>Create Alert</h3>

        <form method="POST" action="{{ route('alerts.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Alert Title</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}" placeholder="e.g. Engine Service" required>

                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Threshold (KM)</label>
                <input type="number" name="threshold" class="form-control @error('threshold') is-invalid @enderror"
                    value="{{ old('threshold') }}" placeholder="e.g. 1000" required>

                @error('threshold')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-success">Save</button>
            <a href="{{ route('alerts.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
