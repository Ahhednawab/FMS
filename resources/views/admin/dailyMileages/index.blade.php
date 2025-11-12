@extends('layouts.admin')

@section('title', 'Daily Mileage List')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage List</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyMileages.create') }}" class="btn btn-primary">
            <span>Add Daily Mileage <i class="icon-plus3 ml-2"></i></span>
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

    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyMileages.index') }}" method="get">
        <div class="row">
          <!-- Vehicle No -->
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label"><strong>Vehicle No</strong></label>
              <select class="custom-select select2 vehicle" name="vehicle_id" id="vehicle_no">
                <option value="">--Select--</option>
                @foreach($vehicles as $value)
                  <option value="{{$value->id}}" {{ request('vehicle_id') == $value->id ? 'selected' : '' }}>{{$value->vehicle_no}}</option>
                @endforeach
              </select>
            </div>
          </div>

          @php
            use Carbon\Carbon;

            $defaultFromDate = request('from_date') ?? Carbon::now()->startOfMonth()->toDateString();
            $defaultToDate = request('to_date') ?? Carbon::now()->today()->toDateString();
          @endphp

          <!--From Date -->
          <div class="col-md-3">
            <div class="form-group">
              <label><strong>From</strong></label>
              <input type="date" class="form-control" name="from_date" value="{{ $defaultFromDate }}" max="{{ date('Y-m-d') }}">
            </div>
          </div>
          <!--To Date -->
          <div class="col-md-3">
            <div class="form-group">
              <label><strong>To</strong></label>
              <input type="date" class="form-control" name="to_date" value="{{ $defaultToDate }}" max="{{ date('Y-m-d') }}">
            </div>
          </div>
          <div class="col-md-3 mt-4">
            <div class="form-group">
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-primary">Reset</a>
            </div>
          </div>

        </div>
        </form>

      </div>
    </div>

    <!-- Basic datatable -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="input-group" style="width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0">
                            <i class="icon-search4"></i>
                        </span>
                    </div>
                    <input type="text" id="customSearch" class="form-control border-left-0" placeholder="Search...">
                </div>
            </div>
            <div>
                <button class="btn btn-light" id="excelBtn" title="Export to Excel">
                    <i class="icon-file-excel"></i> Excel
                </button>
                <button class="btn btn-light" id="printBtn" title="Print">
                    <i class="icon-printer"></i> Print
                </button>

                <button class="btn btn-light ml-2" id="pdfBtn" title="Export PDF">
                    <i class="icon-file-pdf"></i> PDF
                </button>
                <div class="btn-group ml-2">
                    <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="icon-grid7"></i> Columns Visibility<span class="caret"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" id="column-visibility">
                        <h6 class="dropdown-header">Show/Hide Columns</h6>
                        <div class="dropdown-divider"></div>
                        <div class="px-3">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col1" data-column="0" checked>
                                <label class="custom-control-label" for="col1">Vehicle</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col2" data-column="1" checked>
                                <label class="custom-control-label" for="col2">Station</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col3" data-column="2" checked>
                                <label class="custom-control-label" for="col3">Report Date</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col4" data-column="3" checked>
                                <label class="custom-control-label" for="col4">Previous Kms</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col5" data-column="4" checked>
                                <label class="custom-control-label" for="col5">Current Kms</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col6" data-column="5" checked>
                                <label class="custom-control-label" for="col6">Mileage</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input column-toggle" id="col7" data-column="6" checked>
                                <label class="custom-control-label" for="col7">Actions</label>
                            </div>
                        </div>
                    </div>






                </div>
            </div>
        </div>

        <table id="dailyMileages" class="table datatable-colvis-basic dataTable">
          <thead>
            <tr>
              <th>Vehicle</th>
              <th>Station</th>
              <th>Report Date</th>
              <th>Previous Kms</th>
              <th>Current Kms</th>
              <th>Mileage</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dailyMileages as $key => $value)
              <tr>
                <td>{{$value->vehicle->vehicle_no}}</td>
                <td>{{$value->vehicle->station->area}}</td>
                <td>{{ \Carbon\Carbon::parse($value->report_date)->format('d-M-Y') }}</td>
                <td>{{$value->previous_km}} Km</td>
                <td>{{$value->current_km}} Km</td>
                <td>{{$value->mileage}} Km</td>
                <td class="text-center">
                  <div class="list-icons">
                    <div class="dropdown">
                      <a href="#" class="list-icons-item" data-toggle="dropdown">
                        <i class="icon-menu9"></i>
                      </a>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('admin.dailyMileages.show', $value->id) }}" class="dropdown-item">
                          <i class="icon-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.dailyMileages.edit', $value->id) }}" class="dropdown-item">
                          <i class="icon-pencil7"></i> Edit
                        </a>
                        <form action="{{ route('admin.dailyMileages.destroy', $value->id) }}" method="POST">
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

  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="{{ asset('assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

  <script>
    // $(document).ready(function () {
    //   $('.datatable-colvis-basic').DataTable();




    //   $('.vehicle').select2({
    //     placeholder: "--Select--",
    //     allowClear: true,
    //     theme: 'bootstrap4'
    //   });
    // });


    $(document).ready(function () {
        // Initialize DataTable
        var table = $('.datatable-colvis-basic').DataTable({
            dom: "lrtip",
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search...",
                paginate: {
                    previous: '<i class="icon-arrow-left8"></i>',
                    next: '<i class="icon-arrow-right8"></i>'
                }
            },
            columns: [
                { visible: true }, // Vehicle
                { visible: true }, // Station
                { visible: true }, // Report Date
                { visible: true }, // Previous Kms
                { visible: true }, // Current Kms
                { visible: true }, // Mileage
                { visible: true }  // Actions
            ]
        });

        // Initialize DataTable Buttons
        new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'print',
                    text: 'Print',
                    className: 'd-none',
                    exportOptions: {
                        modifier: { page: 'current' }
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'd-none',
                    exportOptions: {
                        modifier: { page: 'current' }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'd-none',
                    exportOptions: {
                        modifier: { page: 'current' }
                    }
                }
            ]
        });

        // Print button
        $('#printBtn').on('click', function() {
            table.button('.buttons-print').trigger();
        });

        // PDF button
        $('#pdfBtn').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });

        // Excel button
        $('#excelBtn').on('click', function() {
            table.button('.buttons-excel').trigger();
        });

        // Custom search input
        $('#customSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Initialize select2
        $('.vehicle').select2({
            placeholder: "--Select--",
            allowClear: true,
            theme: 'bootstrap4'
        });

        // Column toggle
        $('.column-toggle').on('change', function() {
            var column = table.column($(this).data('column'));
            column.visible(!column.visible());
            $(this).prop('checked', column.visible());
        });

        // Sync checkbox state
        table.columns().every(function() {
            $('.column-toggle[data-column="' + this.index() + '"]').prop('checked', this.visible());
        });
    });


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
