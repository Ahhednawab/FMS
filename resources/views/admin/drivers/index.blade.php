@extends('layouts.admin')

@section('title', 'Drivers List')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Drivers List</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
            <span>Add Driver <i class="icon-plus3 ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    @if ($message = Session::get('success'))
      <div id="alert-message" class="alert alert-success alert-dismissible alert-dismissible-2" role="alert">
        {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path class="heroicon-ui" d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z"></path>
          </svg>
        </button>
      </div>
    @elseif ($message = Session::get('delete_msg'))
      <div id="alert-message" class="alert alert-danger alert-dismissible alert-dismissible-2" role="alert">
        {{ $message }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path class="heroicon-ui" d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z"></path>
          </svg>
        </button>
      </div>
    @endif

    <!-- Bulk Actions -->
    <div class="bulk-actions card mb-3" id="bulkActions">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <span id="selectedCount">0</span> items selected
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        Bulk Actions
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" id="deleteSelected">Delete Selected</a>
                        <a class="dropdown-item d-none" href="#" id="exportSelected">Export Selected</a>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-danger ml-auto" id="clearSelection">Clear Selection</button>
            </div>
        </div>
    </div>


    <!-- Basic datatable -->
    <div class="card">
      <div class="card-body">
        <table class="table datatable-colvis-basic dataTable">
          <thead>
            <tr>
              <th width="50">
                <label class="custom-checkbox">
                  <input type="checkbox" id="selectAll">
                  <span class="checkmark"></span>
                </label>
              </th>
              <th>Serial No</th>
              <th>Name</th>
              <th class="text-center">Shift</th>
                <th>Station</th>
              <th>Vehicle</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Father Name</th>
              <th>Mother Name</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($drivers as $key => $value)
              <tr data-id="{{ $value->id }}">
                <td>
                  <label class="custom-checkbox">
                    <input type="checkbox" class="select-checkbox" value="{{ $value->id }}">
                    <span class="checkmark"></span>
                  </label>
                </td>
                <td>{{$value->serial_no}}</td>
                <td>{{$value->full_name}}</td>
                <td class="text-center">
                  @if($value->shiftTiming)
                    {{ $value->shiftTiming->name }}
                    (
                      {{ \Carbon\Carbon::parse($value->shiftTiming->start_time)->format('h:i A') }}
                      -
                      {{ \Carbon\Carbon::parse($value->shiftTiming->end_time)->format('h:i A') }}
                    )
                  @else
                    N/A
                  @endif
                </td>
                  <td>{{ $value->vehicle?->station?->area ?? 'N/A' }}</td>
                <td>@if($value->vehicle) {{$value->vehicle->vehicle_no}} @else N/A @endif</td>
                <td>{{$value->phone}}</td>
                <td>{{$value->driverStatus->name}}</td>
                <td>{{$value->father_name}}</td>
                <td>{{$value->mother_name}}</td>
                <td class="text-center">
                  <div class="list-icons">
                    <div class="dropdown">
                      <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('admin.drivers.show', $value->id) }}" class="dropdown-item"><i class="icon-eye"></i> View</a>
                        <a href="{{ route('admin.drivers.edit', $value->id) }}" class="dropdown-item"><i class="icon-pencil7"></i> Edit</a>
                        <form action="{{ route('admin.drivers.destroy', $value->id) }}" method="POST" class="d-inline">
                          @csrf @method('DELETE')
                          <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure?')"><i class="icon-trash"></i> Delete</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <!-- /basic datatable -->
  </div>
  <!-- /content area -->

  <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>
  <script src="{{ asset('assets/js/demo_pages/datatables_extension_colvis.js') }}"></script>

  <script>
    $(document).ready(function () {
      $('.datatable-colvis-basic').DataTable();
    });

    // Toggle all checkboxes
    $('#selectAll').on('change', function() {
      var isChecked = $(this).prop('checked');
      $('.select-checkbox').prop('checked', isChecked).each(function() {
          $(this).siblings('.checkmark').text(isChecked ? '✓' : '');
      });
      updateBulkActions();
    });

    // Handle individual checkbox changes
    $(document).on('change', '.select-checkbox', function() {
        var isChecked = $(this).prop('checked');
        $(this).siblings('.checkmark').text(isChecked ? '✓' : '');

        // Update select all checkbox
        var allChecked = $('.select-checkbox:checked').length === $('.select-checkbox').length;
        $('#selectAll').prop('checked', allChecked);

        updateBulkActions();
    });

    // Clear selection
    $('#clearSelection').on('click', function() {
      $('.select-checkbox, #selectAll').prop('checked', false);
      $('.checkmark').text('');
      updateBulkActions();
    });

    // Delete selected items
    $('#deleteSelected').on('click', function(e) {
      e.preventDefault();
      var selectedIds = getSelectedIds();

      if (selectedIds.length === 0) {
        alert('Please select at least one driver to delete.');
        return;
      }

      if (confirm('Are you sure you want to delete the selected drivers?')) {
        // Add your delete logic here
        // console.log('Deleting drivers:', selectedIds);
        // Example AJAX call:

        $.ajax({
          url: "{{ route('admin.drivers.destroyMultiple') }}",
          type: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            ids: selectedIds
          },
          success: function(response) {
            if (response.success) {
              location.reload();
            } else {
              alert('Error deleting drivers');
            }
          }
        });

      }
    });

    // Export selected items
    $('#exportSelected').on('click', function(e) {
      e.preventDefault();
      var selectedIds = getSelectedIds();

      if (selectedIds.length === 0) {
        alert('Please select at least one driver to export.');
        return;
      }

      // Add your export logic here
      console.log('Exporting drivers:', selectedIds);
      // Example: window.location.href = '/admin/vehicles/export?ids=' + selectedIds.join(',');
    });

    // Get selected vehicle IDs
    function getSelectedIds() {
      return $('.select-checkbox:checked').map(function() {
        return $(this).val();
      }).get();
    }

    // Update bulk actions visibility and selected count
    function updateBulkActions() {
      var selectedCount = $('.select-checkbox:checked').length;
      $('#selectedCount').text(selectedCount);

      if (selectedCount > 0) {
        $('#bulkActions').addClass('show');
      } else {
        $('#bulkActions').removeClass('show');
      }
    }

    setTimeout(function () {
      let alertBox = document.getElementById('alert-message');
      if (alertBox) {
        alertBox.style.transition = 'opacity 0.5s ease';
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
      }
    }, 3000);
  </script>
@endsection
