@extends('layouts.admin')

@section('title', 'Add Vehicle Attendance')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle Attendance Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-primary">
            <span>View Vehicle Attendance <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.vehicleAttendances.filter') }}" method="POST">
          @csrf
          <div class="row">
            <!-- Vehicle No -->
            <div class="col-md-3">
              <div class="form-group">
                <label class="form-label"><strong>Station</strong></label>
                <select class="custom-select select2" name="station_id" id="station_id">
                  <option value="">ALL</option>
                  @foreach($stations as $key => $value)
                    <option value="{{ $key }}" {{ (isset($selectedStation) && (string)$selectedStation === (string)$key) ? 'selected' : '' }}> {{ $value }} </option>
                  @endforeach
                </select>
              </div>
            </div>

              <div class="col-md-3">
                  <div class="form-group">
                      <label class="form-label"><strong>Vechicle no</strong></label>
                      <select class="custom-select select2" name="vechicle_id" id="vechicle_id">
                          <option value="">ALL</option>
                          @foreach($vehicles as $vehicle)
                              <option value="{{ $vehicle->id }}" {{ old('vechicle_id') == $vehicle->id ? 'selected' : '' }}>
                                  {{ $vehicle->vehicle_no }}
                              </option>
                          @endforeach
                      </select>
                  </div>
              </div>

            <div class="col-md-3 mt-4">
              <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.vehicleAttendances.create') }}" class="btn btn-primary">Reset</a>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.vehicleAttendances.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row mb-3">
            <!-- Date -->
            <div class="col-md-2">
              <div class="form-group">
                <strong>Date </strong>
                <input type="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ old('date') }}" max="{{ date('Y-m-d') }}">
                @error('date')
                  <label class="text-danger">{{ $message }}</label>
                @enderror
              </div>
            </div>

            <!-- Bulk Actions -->
            <div class="col-md-10">
              <div class="form-group">
                <div class="mb-2">
                  <strong>Bulk Actions:</strong>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                  @foreach($attendanceStatus as $id => $status)
                    <button type="button" class="btn btn-sm btn-outline-primary status-btn" data-status-id="{{ $id }}">
                      {{ $status }}
                    </button>
                  @endforeach
                </div>
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="selectAll">
                  <label class="form-check-label font-weight-bold" for="selectAll">Select All Vehicles</label>
                </div>
              </div>
            </div>
          </div>

          @php
            $groupedByStation = collect($vehicleData)->groupBy('station');
            $globalIndex = 0;
          @endphp

          @foreach($groupedByStation as $station => $vehicles)
            <div class="row">
              <!-- Station -->
              <div class="col-md-12">
                <h5 class="mt-3 mb-2">{{ $station }}</h5>
                <hr>
              </div>
            </div>

            @foreach($vehicles as $i => $value)
              <div class="row align-items-center">
                <div class="col-auto pr-0">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input vehicle-checkbox" data-vehicle-id="{{ $value['vehicle_id'] }}">
                  </div>
                </div>
                <input type="hidden" class="form-control" name="vehicle_id[]" value="{{ $value['vehicle_id'] }}">

                <!-- Vehicle No -->
                <div class="col-md-1 pl-0">
                  <div class="form-group">
                    <strong>Vehicle No</strong>
                    <input type="text" class="form-control" name="vehicle_no" value="{{ $value['vehicle_no'] }}" readonly>
                  </div>
                </div>

                <!-- Make (Manufacturer) -->
                <div class="col-md-2">
                  <div class="form-group">
                    <strong>Make (Manufacturer)</strong>
                    <input type="text" class="form-control" name="make" value="{{ $value['make'] }}" readonly>
                  </div>
                </div>

                <!-- Shift -->
                <div class="col-md-1">
                  <div class="form-group">
                    <strong>Shift</strong>
                    <input type="text" class="form-control" name="shift" value="{{ $value['shift'] }}" readonly>
                  </div>
                </div>

                <!-- IBC Center -->
                <div class="col-md-1">
                  <div class="form-group">
                    <strong>IBC Center</strong>
                    <input type="text" class="form-control" name="ibcCenter" value="{{ $value['ibcCenter'] }}" readonly>
                  </div>
                </div>

                <!-- Attendance -->
                  <div class="col-md-2">
                      <div class="form-group">
                          <strong>Attendance</strong>
                          <select class="custom-select attendanceStatus @error('status.' . $value['vehicle_id']) is-invalid @enderror" name="status[{{ $value['vehicle_id'] }}]" id="attendanceStatus-{{ $value['vehicle_id'] }}">
                              <option value="">Select</option>
                              @foreach($attendanceStatus as $statusKey => $statusLabel)
                                  <option value="{{ $statusKey }}"
                                      {{ old('status.' . $value['vehicle_id']) == (string) $statusKey ? 'selected' : '' }}>
                                      {{ $statusLabel }}
                                  </option>
                              @endforeach
                          </select>
                          @error('status.' . $value['vehicle_id'])
                          <label class="text-danger">{{ $message }}</label>
                          @enderror
                      </div>
                  </div>

                  <!-- Additional dropdown or section that will be conditionally displayed -->
                  <div id="additionalOptions-{{ $value['vehicle_id'] }}" class="col-md-2 additionalOptions" style="display: none;">
                      <div class="form-group">
                          <strong>Pool Vechicle</strong>
                          <select class="custom-select select2 pool_id" name="pool_id[{{ $value['vehicle_id'] }}]" id="pool_id">
                              <option value="" >ALL</option>
                              @foreach($poolvehicles as $station)
                                  <option value="{{ $station->id }}" {{ old('pool_id') == $station->id ? 'selected' : '' }}>
                                      {{ $station->vehicle_no }}
                                  </option>
                              @endforeach
                          </select>
                      </div>
                  </div>


              </div>



              @php $globalIndex++; @endphp
            @endforeach
          @endforeach

          <div class="row">
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.vehicleAttendances.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    function removeElementOnce(arr, value) {
      const index = arr.indexOf(value);
      if (index > -1) { // Only splice if the value is found
        arr.splice(index, 1); // Remove 1 element at the found index
      }
      return arr;
    }
    // Handle status button clicks
    $('.status-btn').on('click', function() {
        const statusId = $(this).data('status-id');
        const statusName = $(this).text().trim();

        // Find all checked checkboxes
        const $checkedBoxes = $('.vehicle-checkbox:checked');

        if ($checkedBoxes.length === 0) {
            // Show error if no vehicles are selected
            new Noty({
                type: 'error',
                text: 'Please select at least one vehicle',
                timeout: 3000
            }).show();
            return;
        }

        // Update status for each selected vehicle
        $checkedBoxes.each(function() {
            const vehicleId = $(this).data('vehicle-id');
            $(`select[name='status[${vehicleId}]']`).val(statusId);
        });

        // Show success message
        new Noty({
            type: 'success',
            text: `Updated status to ${statusName} for ${$checkedBoxes.length} vehicle(s)`,
            timeout: 3000
        }).show();
    });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.vehicle-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Uncheck "Select All" if any checkbox is unchecked
    $('.vehicle-checkbox').on('change', function() {
        if (!$(this).prop('checked')) {
            $('#selectAll').prop('checked', false);
        } else {
            // If all checkboxes are checked, check "Select All"
            if ($('.vehicle-checkbox:not(:checked)').length === 0) {
                $('#selectAll').prop('checked', true);
            }
        }
    });



  
});
$(document).ready(function() {
    // Your existing code for status buttons, select all, etc. remains unchanged...

    // === NEW: Track selected pool vehicles globally ===
    let selectedPoolVehicles = []; // Array to store currently selected pool vehicle IDs

    // Function to refresh ALL pool dropdowns based on what's already selected
    function refreshAllPoolDropdowns() {
        $('.pool_id').each(function() {
            const $select = $(this);
            const currentValue = $select.val();
            const vehicleId = $select.closest('[id^="additionalOptions-"]').attr('id').split('-')[1];

            // Get all options
            const $options = $select.find('option:not(:first-child)'); // exclude "ALL"

            $options.each(function() {
                const $option = $(this);
                const poolVehicleId = $option.val();

                if (poolVehicleId === '') return;

                // Disable option if it's selected in another row (and not this one)
                if (selectedPoolVehicles.includes(poolVehicleId) && poolVehicleId !== currentValue) {
                    $option.prop('disabled', true);
                    $option.text($option.data('original-text') + ' (Already Assigned)');
                } else {
                    $option.prop('disabled', false);
                    $option.text($option.data('original-text')); // restore original text
                }
            });

            // Reinitialize Select2 to reflect disabled state
            $select.trigger('change.select2');
        });
    }

    // On page load: store original text and initialize
    $('.pool_id').each(function() {
        $(this).find('option:not(:first-child)').each(function() {
            const text = $(this).text();
            $(this).data('original-text', text);
        });
    });

    // Listen for changes on any pool vehicle dropdown
    $(document).on('change', '.pool_id', function() {
        const selectedValue = $(this).val();
        const previousValue = $(this).data('previous-value') || '';

        // Remove previously selected value (if any and if it was valid)
        if (previousValue && previousValue !== '') {
            selectedPoolVehicles = selectedPoolVehicles.filter(id => id !== previousValue);
        }

        // Add newly selected value (if not empty)
        if (selectedValue && selectedValue !== '') {
            // Prevent duplicate selection
            if (selectedPoolVehicles.includes(selectedValue)) {
                // Revert selection
                $(this).val('');
                new Noty({
                    type: 'error',
                    text: 'This pool vehicle is already assigned to another vehicle!',
                    timeout: 4000
                }).show();
            } else {
                selectedPoolVehicles.push(selectedValue);
            }
        }

        // Store current value for next change
        $(this).data('previous-value', $(this).val());

        // Refresh all dropdowns
        refreshAllPoolDropdowns();
    });


    $('body').on('change','.attendanceStatus',function(){
      let attendance = $(this).val();
      $(this).parent().parent().closest('.additionalOptions select').val("");

      if(attendance==5 || attendance==6)
      {
        $(this).parent().parent().closest('.additionalOptions').show();
      }else
      {
        $(this).parent().parent().closest('.additionalOptions').hide();

      }
    });
    // Existing attendance status change handler (updated to trigger pool refresh)
//     document.querySelectorAll('[id^="attendanceStatus-"]').forEach(function (attendanceStatusSelect) {
//         const vehicleId = attendanceStatusSelect.id.split('-')[1];
//         const additionalOptionsDiv = document.getElementById('additionalOptions-' + vehicleId);
//         const poolSelect = document.getElementById('pool_id'); // Note: all have same ID — fix this!

//         // WARNING: You have duplicate IDs! All pool dropdowns use id="pool_id"
//         // This is invalid HTML and breaks JS targeting.
//         // Fix: Give each a unique ID like: pool_id-{{ $value['vehicle_id'] }}

//         function toggleAdditionalOptions() {
//             const selectedValue = attendanceStatusSelect.value;
//             if (selectedValue === '5' || selectedValue === '6') {
//                 additionalOptionsDiv.style.display = 'block';

//                 // Trigger change to sync disabled state when shown
//                 setTimeout(() => {
//                     $('.pool_id:visible').trigger('change');
//                 }, 100);
//             } else {
//                 additionalOptionsDiv.style.display = 'none';

//                 // Clear selection and free up the pool vehicle when hidden
//                 const $poolSelect = $(additionalOptionsDiv).find('.pool_id');
//                 const wasSelected = $poolSelect.val();
//                 if (wasSelected && wasSelected !== '') {
//                     selectedPoolVehicles = selectedPoolVehicles.filter(id => id !== wasSelected);
//                 }
//                 $poolSelect.val('').trigger('change');
//                 refreshAllPoolDropdowns();
//             }
//         }

//         attendanceStatusSelect.addEventListener('change', toggleAdditionalOptions);
//         toggleAdditionalOptions(); // initial check
//     });

//     // Initial refresh in case some are pre-selected
//     refreshAllPoolDropdowns();
});



document.addEventListener('DOMContentLoaded', function () {
    // Select all attendance dropdowns based on their ID pattern
    document.querySelectorAll('[id^="attendanceStatus-"]').forEach(function (attendanceStatusSelect) {
        const vehicleId = attendanceStatusSelect.id.split('-')[1]; // Extract vehicle ID from the dropdown ID
        const additionalOptionsDiv = document.getElementById('additionalOptions-' + vehicleId);

        // Function to toggle the additional options based on the selected status
        function toggleAdditionalOptions() {
            const selectedValue = attendanceStatusSelect.value;  // Get the value of the selected option
            console.log(selectedValue);
            if (selectedValue === '5' || selectedValue === '6') {
                additionalOptionsDiv.style.display = 'block';  // Show the additional options
            } else {
                additionalOptionsDiv.style.display = 'none';  // Hide the additional options
            }
        }

        // Add event listener for the 'change' event on the attendance status dropdown
        attendanceStatusSelect.addEventListener('change', toggleAdditionalOptions);

        // Initial check in case a value is already selected when the page loads
        toggleAdditionalOptions();
    });
});
</script>

@endpush
