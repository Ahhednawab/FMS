@extends('layouts.admin')

@section('title', 'Add Daily Mileage')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Daily Mileage Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-primary">
            <span>View Daily Mileage <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.dailyMileages.store') }}" method="POST">
          @csrf
          @foreach($vehicleData as $key => $value)
            <div class="row kilometer">
              <input type="hidden" class="form-control" name="vehicle_id[]" value="{{ $value['vehicle_id'] }}">
              <!-- Vehicle No -->
              <div class="col-md-3">
                <div class="form-group">
                  <strong>Vehicle No</strong>
                  <input type="text" class="form-control" name="vehicle_no" value="{{ $value['vehicle_no'] }}" readonly>
                  @if ($errors->has('vehicle_id'))
                    <label class="text-danger">{{ $errors->first('vehicle_id') }}</label>
                  @endif
                </div>
              </div>

              <!-- Previous KMs -->
              <div class="col-md-3">
                <div class="form-group">
                  <strong>Previous KMs</strong>
                  <input type="number" min="0" step="1" class="form-control previous_km" name="previous_km[]" value="{{ $value['previous_km'] }}" readonly>
                  @if ($errors->has('previous_km'))
                    <label class="text-danger">{{ $errors->first('previous_km') }}</label>
                  @endif               
                </div>
              </div>
              
              <!-- Current KMs -->
              <div class="col-md-3">
                <div class="form-group">
                  <strong>Current KMs</strong>
                  <input type="number" min="0" step="1" class="form-control current_km" name="current_km[]" value="{{ old('current_km.' . $loop->index) }}">
                  @if ($errors->has('current_km.' . $loop->index))
                    <label class="text-danger">{{ $errors->first('current_km.' . $loop->index) }}</label>
                  @endif
                </div>
              </div>

              <!-- Mileage -->
              <div class="col-md-3">
                <div class="form-group">
                  <strong>Mileage</strong>
                  <input type="number" min="0" step="1" class="form-control" name="mileage[]" value="{{ old('mileage.' . $loop->index) }}" readonly>
                  @if ($errors->has('mileage.' . $loop->index))
                    <label class="text-danger">{{ $errors->first('mileage.' . $loop->index) }}</label>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
          <div class="row">
            <div class="col-md-12">
              <label for=""></label>
              <div class="text-right">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.dailyMileages.index') }}" class="btn btn-warning">Cancel</a>
              </div>
            </div>
          </div>                    
        </form>
      </div>
    </div>
  </div>

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    $(document).ready(function () {
        // $('#vehicle_id').select2({
        //     placeholder: "--Select--",
        //     allowClear: true,
        //     theme: 'bootstrap4'
        // });

        function calculateMileage() {
            $('.kilometer').each(function() {
                var previous_km = $(this).find('.previous_km').val() || 0;
                var current_km = $(this).find('.current_km').val() || 0;
                var mileage = current_km - previous_km;
                if (mileage < 0) mileage = 0;
                if(previous_km){
                  previous_km = current_km;
                }
                $(this).find('input[name="mileage[]"]').val(mileage.toFixed(0))
            });

        }

        $('.current_km').on('input', function(){
            calculateMileage();
        });

    });

    
  </script>
@endsection
