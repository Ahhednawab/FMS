@extends('layouts.admin')

@section('title', 'Edit Accident Detail')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Accident Detail</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('accidentDetails.index') }}" class="btn btn-primary">
                        <span>View Accident Detail <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('accidentDetails.update', $accidentDetail->id) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Accident ID</label>
                                        <input value="{{ $accidentDetail->accident_id }}" name="accident_id" type="text"
                                            class="form-control" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Vehicle No <span class="text-danger">*</span></label>
                                        <select class="custom-select" id="vehicle_no" name="vehicle_no" required>
                                            <option value="">--Select Vehicle--</option>
                                            @foreach ($vehicles as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('vehicle_no', $accidentDetail->vehicle_no ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('vehicle_no'))
                                            <label class="text-danger">{{ $errors->first('vehicle_no') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Insurance</label>
                                        <input type="text" id="insurance" class="form-control" name="insurance"
                                            value="{{ old('insurance', $accidentDetail->insurance ?? '') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Ownership</label>
                                        <input type="text" id="ownership" class="form-control" name="ownership"
                                            value="{{ old('ownership', $accidentDetail->ownership ?? '') }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Driver Name</label>
                                        <input type="text" id="driver_name" class="form-control" name="driver_name"
                                            value="{{ old('driver_name', $accidentDetail->driver_name ?? '') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Licence No</label>
                                        <input type="text" id="licence_no" class="form-control" name="licence_no"
                                            value="{{ old('licence_no', $accidentDetail->licence_no ?? '') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Policy No</label>
                                        <input type="text" id="policy_no" class="form-control" name="policy_no"
                                            value="{{ old('policy_no', $accidentDetail->policy_no ?? '') }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Workshop <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="workshop"
                                            value="{{ old('workshop', $accidentDetail->workshop ?? '') }}" required>
                                        @if ($errors->has('workshop'))
                                            <label class="text-danger">{{ $errors->first('workshop') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Third Party <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="third_party" rows="4" required>{{ old('third_party', $accidentDetail->third_party ?? '') }}</textarea>
                                        @if ($errors->has('third_party'))
                                            <label class="text-danger">{{ $errors->first('third_party') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Remarks <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="remarks" rows="4" required>{{ old('remarks', $accidentDetail->remarks ?? '') }}</textarea>
                                        @if ($errors->has('remarks'))
                                            <label class="text-danger">{{ $errors->first('remarks') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Claim Amount <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="claim_amount"
                                            value="{{ old('claim_amount', $accidentDetail->claim_amount ?? 0) }}"
                                            required>
                                        @if ($errors->has('claim_amount'))
                                            <label class="text-danger">{{ $errors->first('claim_amount') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Depreciation Amount<span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="depreciation_amount"
                                            value="{{ old('depreciation_amount', $accidentDetail->depreciation_amount ?? 0) }}"
                                            required>
                                        @if ($errors->has('depreciation_amount'))
                                            <label class="text-danger">{{ $errors->first('depreciation_amount') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Payment Status <span class="text-danger">*</span></label>
                                        <select class="custom-select" name="payment_status" required>
                                            <option value="">--Select--</option>
                                            @foreach ($payment_statuses as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ old('payment_status', $accidentDetail->payment_status ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('payment_status'))
                                            <label class="text-danger">{{ $errors->first('payment_status') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" class="custom-control-input" id="bill_to_ke"
                                                name="bill_to_ke" value="1"
                                                {{ old('bill_to_ke', $accidentDetail->bill_to_ke ?? 0) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="bill_to_ke">
                                                Bill to KE
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Files & Images Upload</label>
                                        <div class="custom-file-upload border-dashed rounded p-3 text-center"
                                            id="dropZone">
                                            <input type="file" id="fill_image" name="fill_image[]" multiple
                                                accept=".jpg,.jpeg,.png,.gif,.pdf" style="display: none;">
                                            <i class="icon-cloud-upload icon-2x text-muted mb-2"></i>
                                            <p class="mb-0">Drag & drop files here or click to select</p>
                                            <small class="text-muted">Supported formats: JPEG, PNG, JPG, GIF, PDF (Max 5MB
                                                per file)</small>
                                        </div>

                                        <!-- Display existing files -->
                                        @if ($accidentDetail->files && $accidentDetail->files->count() > 0)
                                            <div class="mt-3">
                                                <h6 class="font-weight-bold mb-2">Existing Files:</h6>
                                                <div id="existingFileList">
                                                    @foreach ($accidentDetail->files as $file)
                                                        <div class="alert alert-light d-flex justify-content-between align-items-center"
                                                            id="existing-file-{{ $file->id }}">
                                                            <span>
                                                                @if ($file->file_type === 'image')
                                                                    <img src="{{ asset($file->file_path) }}"
                                                                        alt="{{ $file->original_name }}" width="30"
                                                                        class="mr-2 rounded">
                                                                @else
                                                                    <i class="icon-file-pdf text-danger mr-2"></i>
                                                                @endif
                                                                {{ $file->original_name }}
                                                                ({{ number_format($file->file_size / 1024, 2) }} KB)
                                                            </span>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteFile({{ $file->id }}, '{{ $file->file_path }}')">
                                                                <i class="icon-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div id="fileList" class="mt-3">
                                            <!-- New file previews will be added here -->
                                        </div>
                                        @if ($errors->has('fill_image'))
                                            <label class="text-danger">{{ $errors->first('fill_image') }}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <a href="{{ route('accidentDetails.index') }}" class="btn btn-warning">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch vehicle information on change
        document.getElementById('vehicle_no').addEventListener('change', function() {
            const vehicleNo = this.value;
            if (vehicleNo) {
                fetch(`/admin/accidentDetails/get-vehicle-info/${vehicleNo}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert('Vehicle not found');
                            return;
                        }
                        document.getElementById('insurance').value = data.insurance || '';
                        document.getElementById('ownership').value = data.ownership || '';
                        document.getElementById('driver_name').value = data.driver_name || '';
                        document.getElementById('licence_no').value = data.licence_no || '';
                        document.getElementById('policy_no').value = data.policy_no || '';
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                document.getElementById('insurance').value = '';
                document.getElementById('ownership').value = '';
                document.getElementById('driver_name').value = '';
                document.getElementById('licence_no').value = '';
                document.getElementById('policy_no').value = '';
            }
        });

        // File upload handling
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fill_image');
        const fileList = document.getElementById('fileList');
        let selectedFiles = [];

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-light');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('bg-light');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-light');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            selectedFiles = Array.from(files);
            displayFiles();
        }

        function displayFiles() {
            fileList.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'alert alert-info d-flex justify-content-between align-items-center';
                fileItem.innerHTML = `
                    <span>${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">Remove</button>
                `;
                fileList.appendChild(fileItem);
            });
            updateFileInput();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displayFiles();
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        function deleteFile(fileId, filePath) {
            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            fetch(`/admin/accidentDetails/delete-file/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`existing-file-${fileId}`).remove();
                        alert('File deleted successfully');
                    } else {
                        alert('Error deleting file');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting file');
                });
        }
    </script>

    <style>
        .border-dashed {
            border: 2px dashed #ccc !important;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .border-dashed:hover {
            border-color: #007bff !important;
            background-color: #f8f9fa;
        }

        .border-dashed.bg-light {
            background-color: #e7f3ff !important;
            border-color: #007bff !important;
        }
    </style>

@endsection
