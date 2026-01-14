@extends('layouts.admin')

@section('title', 'Add Accident Details')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Accident Details
                        Management</span></h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('accidentDetails.index') }}" class="btn btn-primary">
                        <span>View Accident Details <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('accidentDetails.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Accident ID</label>
                                <input value="{{ $accident_id }}" name="accident_id" type="text" class="form-control"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Vehicle No <span class="text-danger">*</span></label>
                                <select class="custom-select" id="vehicle_no" name="vehicle_no" required>
                                    <option value="">--Select Vehicle--</option>
                                    @foreach ($vehicles as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('vehicle_no') == $key ? 'selected' : '' }}>{{ $value }}
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
                                <input type="text" id="insurance" class="form-control" name="insurance" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Ownership</label>
                                <input type="text" id="ownership" class="form-control" name="ownership" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Driver Name</label>
                                <input type="text" id="driver_name" class="form-control" name="driver_name" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Licence No</label>
                                <input type="text" id="license_no" class="form-control" name="license_no" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Policy No</label>
                                <input type="text" id="policy_no" class="form-control" name="policy_no" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Workshop <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="workshop" value="{{ old('workshop') }}"
                                    required>
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
                                <textarea class="form-control" name="third_party" rows="4" required>{{ old('third_party') }}</textarea>
                                @if ($errors->has('third_party'))
                                    <label class="text-danger">{{ $errors->first('third_party') }}</label>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="remarks" rows="4" required>{{ old('remarks') }}</textarea>
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
                                    value="{{ old('claim_amount') }}" required>
                                @if ($errors->has('claim_amount'))
                                    <label class="text-danger">{{ $errors->first('claim_amount') }}</label>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Depreciation Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="depreciation_amount"
                                    value="{{ old('depreciation_amount') }}" required>
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
                                            {{ old('payment_status') == $key ? 'selected' : '' }}>{{ $value }}
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
                                        name="bill_to_ke" value="1" {{ old('bill_to_ke') ? 'checked' : '' }}>
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
                                <div class="custom-file-upload border-dashed rounded p-3 text-center" id="dropZone">
                                    <input type="file" id="fill_image" name="fill_image[]" multiple accept="image/*"
                                        style="display: none;">
                                    <i class="icon-cloud-upload icon-2x text-muted mb-2"></i>
                                    <p class="mb-0">Drag & drop files here or click to select</p>
                                    <small class="text-muted">Supported formats: JPEG, PNG, JPG, GIF (Max 5MB per
                                        file)</small>
                                </div>
                                <div id="fileList" class="mt-3">
                                    <!-- File previews will be added here -->
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
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('accidentDetails.index') }}" class="btn btn-warning">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
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
                        document.getElementById('license_no').value = data.license_no || '';
                        document.getElementById('policy_no').value = data.policy_no || '';
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                document.getElementById('insurance').value = '';
                document.getElementById('ownership').value = '';
                document.getElementById('driver_name').value = '';
                document.getElementById('license_no').value = '';
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
