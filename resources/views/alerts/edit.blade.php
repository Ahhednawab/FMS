@extends('layouts.admin')

@section('content')
    <div class="container">
        <h3>Edit Alert</h3>

        <form method="POST" action="{{ route('alerts.update', $alert->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Alert Title</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $alert->title) }}" required>

                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Threshold (KM)</label>
                <input type="number" name="threshold" class="form-control @error('threshold') is-invalid @enderror"
                    value="{{ old('threshold', $alert->threshold) }}" required>

                @error('threshold')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-success">Update</button>
            <a href="{{ route('alerts.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
