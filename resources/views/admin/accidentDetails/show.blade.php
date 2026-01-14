@extends('layouts.admin')

@section('title', 'Accident Detail')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Accident Detail</span></h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('accidentDetails.index') }}" class="btn btn-primary"><span>View Accident Details <i
                                class="icon-list ml-2"></i></span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <!-- Content area -->
    <div class="content">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Accident Details Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Accident ID -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Accident ID:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->accident_id }}</p>
                        </div>
                    </div>

                    <!-- Vehicle No -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Vehicle No:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->vehicle_no }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Insurance -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Insurance:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->insurance ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Ownership -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Ownership:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->ownership ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Driver Name -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Driver Name:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->driver_name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Licence No -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Licence No:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->licence_no ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Policy No -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Policy No:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->policy_no ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Workshop -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Workshop:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->workshop }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Third Party -->
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Third Party:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->third_party }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Claim Amount -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Claim Amount:</label>
                            <p class="form-control-plaintext">{{ number_format($accidentDetail->claim_amount) }}</p>
                        </div>
                    </div>

                    <!-- Depreciation Amount -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Depreciation Amount:</label>
                            <p class="form-control-plaintext">{{ number_format($accidentDetail->depreciation_amount) }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Remarks -->
                    <div class="col-md-12 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Remarks:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->remarks }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Bill to KE -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Bill to KE:</label>
                            <p class="form-control-plaintext">
                                @if ($accidentDetail->bill_to_ke)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Payment Status:</label>
                            <p class="form-control-plaintext">
                                <span
                                    class="badge badge-{{ $accidentDetail->payment_status === 'pending' ? 'warning' : 'success' }}">
                                    {{ ucfirst($accidentDetail->payment_status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Created By -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Created By:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->creator->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Created At:</label>
                            <p class="form-control-plaintext">{{ $accidentDetail->created_at->format('d-M-Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Files Section -->
                @if ($accidentDetail->files && $accidentDetail->files->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Uploaded Files:</label>
                                <div class="mt-2">
                                    @foreach ($accidentDetail->files as $file)
                                        <div
                                            class="alert alert-light border d-flex justify-content-between align-items-center">
                                            <span>
                                                @if ($file->file_type === 'image')
                                                    <a href="{{ asset($file->file_path) }}" target="_blank">
                                                        <img src="{{ asset($file->file_path) }}"
                                                            alt="{{ $file->original_name }}" width="30"
                                                            class="mr-2 rounded">
                                                    </a>
                                                @else
                                                    <a href="{{ asset($file->file_path) }}" target="_blank">
                                                        <i class="icon-file-pdf text-danger mr-2 icon-2x"></i>
                                                    </a>
                                                @endif

                                                <a href="{{ asset($file->file_path) }}" target="_blank">
                                                    {{ $file->original_name }}
                                                </a>

                                                <small class="text-muted">
                                                    ({{ number_format($file->file_size / 1024, 2) }} KB)
                                                </small>
                                            </span>

                                            <!-- Download Button -->
                                            <a href="{{ asset($file->file_path) }}"
                                                download="{{ $file->original_name }}" class="btn btn-sm btn-success">
                                                <i class="icon-download mr-1"></i> Download
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('accidentDetails.edit', $accidentDetail->id) }}" class="btn btn-warning">
                            <i class="icon-pencil7 mr-1"></i> Edit
                        </a>
                        <a href="{{ route('accidentDetails.index') }}" class="btn btn-secondary">
                            <i class="icon-list mr-1"></i> Back
                        </a>
                        <form action="{{ route('accidentDetails.destroy', $accidentDetail->id) }}" method="POST"
                            style="display:inline-block;"
                            onsubmit="return confirm('Are you sure you want to delete this Accident Details?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger">
                                <i class="icon-trash mr-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /content area -->
@endsection
