@extends('layouts.admin')

@section('title', 'Vehicle List')

@push('styles')
<style>
    .bulk-actions {
        margin-bottom: 15px;
        display: none;
    }
    .bulk-actions.show {
        display: block;
    }
    .select-all-checkbox {
        margin-right: 10px;
    }

    /* Custom checkbox styling */
    .custom-checkbox {
      position: relative;
      display: inline-block;
      padding-left: 25px;
      cursor: pointer;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }

    .custom-checkbox input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
      height: 0;
      width: 0;
    }

    .checkmark {
      position: absolute;
      top: 50%;
      left: 0;
      transform: translateY(-50%);
      height: 18px;
      width: 18px;
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 3px;
      text-align: center;
      line-height: 18px;
      color: white;
      font-size: 12px;
    }

    .custom-checkbox:hover input ~ .checkmark {
      background-color: #f1f1f1;
    }

    .custom-checkbox input:checked ~ .checkmark {
      background-color: #2196F3;
      border-color: #2196F3;
    }

    .checkmark:after {
      display: none !important;
    }

    .custom-checkbox input:checked ~ .checkmark:after {
      content: "";
      position: absolute;
      display: block;
      left: 6px;
      top: 2px;
      width: 5px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }

    .custom-checkbox .checkmark:after {
        left: 6px;
        top: 2px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    /* Center checkboxes in cells */
    .form-check {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        margin: 0;
        padding: 0;
    }
    .custom-checkbox input[type="checkbox"]:checked + .checkmark {
      background-color: #2196F3;
      border-color: #2196F3;
    }

    .custom-checkbox input[type="checkbox"]:checked + .checkmark:after {
      display: block;
    }

    /* Make sure checkmark is visible */
    .checkmark:after {
      content: "";
      position: absolute;
      display: none;
      left: 6px;
      top: 2px;
      width: 5px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }
</style>
@endpush

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Vehicle List</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary">
            <span>Add Vehicle <i class="icon-plus3 ml-2"></i></span>
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
          <button class="btn btn-light" id="excelBtn" title="Export to Excel">
              <i class="icon-file-excel"></i> Excel
          </button>
          <button class="btn btn-light" id="printBtn" title="Print">
              <i class="icon-printer"></i> Print
          </button>
          <button class="btn btn-light ml-2" id="pdfBtn" title="Export PDF">
              <i class="icon-file-pdf"></i> PDF
          </button>
        <table class="table datatable-colvis-basic dataTable">
          <thead>
            <tr>
              <th width="50">
                <label class="custom-checkbox">
                  <input type="checkbox" id="selectAll">
                  <span class="checkmark"></span>
                </label>
              </th>
              <th>Vehicle No</th>
                <th>Make</th>
              <th>Model</th>
                <th>Ownership </th>
                <th>AKPL</th>
              <th>Shift</th>
              <th>Vehicle Type</th>
                <th>Fabricator</th>
              <th>Station</th>
              <th>IBC</th>
                <th>Inspection Date</th>
                <th>Next Inspection Date</th>
                <th>Induction Date </th>
                <th>Fitness Date</th>
                <th>Next fitness date</th>
                <th>Insurance Expiry Date</th>
                <th>RP date</th>
                <th>RP expiry date</th>
                <th>Next tax date</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($vehicles as $key => $value)
              <tr data-id="{{ $value->id }}">
                <td>
                  <label class="custom-checkbox">
                    <input type="checkbox" class="select-checkbox" value="{{ $value->id }}">
                    <span class="checkmark"></span>
                  </label>
                </td>
                <td>{{$value->vehicle_no}}</td>
                  <td>{{$value->make}}</td>
                <td>{{$value->model}}</td>
                  <td>{{$value->ownership }}</td>
                  <td>{{$value->akpl}}</td>
                <td>@if($value->shiftHours) {{$value->shiftHours->name}} @else N/A @endif</td>
                <td>{{$value->vehicleType->name}}</td>

                  <td>{{ $value->fabricationVendor?->name ?? 'N/A' }}</td>
                  <td>{{$value->station->area}}</td>
                  <td>{{$value->ibcCenter->name}}</td>
                  <td>{{$value->inspection_date}}</td>
                  <td>{{$value->next_inspection_date}}</td>
                  <td>{{$value->induction_date}}</td>
                  <td>{{$value->fitness_date}}</td>
                  <td>{{$value->next_fitness_date}}</td>
                  <td>{{$value->insurance_expiry_date}}</td>
                <td>{{$value->route_permit_date}}</td>
                <td>{{$value->route_permit_expiry_date}}</td>
                  <td>{{$value->next_tax_date}}</td>
                <td class="text-center">
                  <div class="list-icons">
                    <div class="dropdown">
                      <a href="#" class="list-icons-item" data-toggle="dropdown">
                        <i class="icon-menu9"></i>
                      </a>

                      <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('admin.vehicles.show', $value->id) }}" class="dropdown-item">
                          <i class="icon-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.vehicles.edit', $value->id) }}" class="dropdown-item">
                          <i class="icon-pencil7"></i> Edit
                        </a>
                        <form action="{{ route('admin.vehicles.destroy', $value->id) }}" method="POST">
                          @csrf
                          @method('DELETE')
                          <button class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete?')">
                            <i class="icon-trash"></i> Delete
                          </button>
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
      // Initialize DataTable
      var table = $('.datatable-colvis-basic').DataTable();
      //   {
      //   columnDefs: [
      //     { orderable: false, targets: [0, 8] } // Disable sorting on checkbox and action columns
      //   ]
      // }
    // );

        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'print',
                    text: 'Print',
                    className: 'd-none',
                    exportOptions: {
                        modifier: { page: 'all' }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'd-none',
                    exportOptions: {
                        modifier: { page: 'all' }
                    }
                },
                {
                    extend: 'excelHtml5',   // <<< Excel button added
                    text: 'Excel',
                    className: 'd-none',
                    exportOptions: {
                        modifier: { page: 'all' }
                    }
                }
            ]
        });

        // Button triggers
        $('#printBtn').on('click', function() {
            table.button('.buttons-print').trigger();
        });

        $('#pdfBtn').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });

        $('#excelBtn').on('click', function() {   // <<< Excel button trigger
            table.button('.buttons-excel').trigger();
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
          alert('Please select at least one vehicle to delete.');
          return;
        }

        if (confirm('Are you sure you want to delete the selected vehicles?')) {
          // Add your delete logic here
          // console.log('Deleting vehicles:', selectedIds);
          // Example AJAX call:

          $.ajax({
            url: "{{ route('admin.vehicles.destroyMultiple') }}",
            type: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              ids: selectedIds
            },
            success: function(response) {
              if (response.success) {
                location.reload();
              } else {
                alert('Error deleting vehicles');
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
          alert('Please select at least one vehicle to export.');
          return;
        }

        // Add your export logic here
        console.log('Exporting vehicles:', selectedIds);
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
    });

    // Auto-hide alert message
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
