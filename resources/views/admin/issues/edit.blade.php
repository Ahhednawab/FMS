@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4>Edit Issue</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('issues.update', $issue->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Issue Title --}}
                    <div class="form-group mb-3">
                        <label for="title">Issue Title</label>
                        <input type="text" name="title" id="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $issue->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="form-group mb-3">
                        <label for="is_active">Status</label>
                        <select name="is_active" id="is_active"
                            class="form-control @error('is_active') is-invalid @enderror">
                            <option value="1" {{ old('is_active', $issue->is_active) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ old('is_active', $issue->is_active) == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('issues.index') }}" class="btn btn-secondary me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary ml-2">
                            Update Issue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
