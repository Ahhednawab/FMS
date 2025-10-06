@extends('layouts.admin')

@section('title', 'Edit Daily Fuel')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
  .select2-search--dropdown::after {
    content: '' !important;
    display: none !important;
    background: none !important;
  }
</style>

<div class="page-header page-header-light">
  <div class="page-header-content header-elements-lg-inline">
    <div class="page-title d-flex">
      <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Edit Daily Fuel</span></h4>
      <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
    </div>
    <div class="header-elements d-none">
      <div class="d-flex justify-content-center">
        <a href="{{ route('admin.dailyFuels.index') }}" class="btn btn-primary">
          <span>View Daily Fuel <i class="icon-list ml-2"></i></span>
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
          <form method="POST" action="{{ route('admin.dailyFuels.update', $dailyFuel->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
              <!-- Vehicle No -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Vehicle No</strong>
                  <input type="text" class="form-control" name="vehicle_no" value="{{ old('vehicle_id', $dailyFuel->vehicle->vehicle_no ?? '') }}" readonly>
                  
                </div>
              </div>

              <!-- Report Date -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Report Date</strong>
                  <input type="date" class="form-control" name="report_date" value="{{ old('report_date', $dailyFuel->report_date ?? '') }}">
                  @if ($errors->has('report_date'))
                  <label class="text-danger">{{ $errors->first('report_date') }}</label>
                  @endif
                </div>
              </div>

              <!-- Previous Km -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Previous Km</strong>
                  <input type="number" min="0" step="1" class="form-control" name="previous_km" value="{{ old('previous_km', $dailyFuel->previous_km ?? '') }}" readonly>
                  @if ($errors->has('previous_km'))
                  <label class="text-danger">{{ $errors->first('previous_km') }}</label>
                  @endif
                </div>
              </div>

              <!-- Current Km -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Current Km</strong>
                  <input type="number" min="0" step="1" class="form-control" name="current_km" value="{{ old('current_km', $dailyFuel->current_km ?? '') }}">
                  @if ($errors->has('current_km'))
                  <label class="text-danger">{{ $errors->first('current_km') }}</label>
                  @endif
                </div>
              </div>

              <!-- Mileage -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Mileage</strong>
                  <input type="number" min="0" step="1" class="form-control" name="mileage" value="{{ old('mileage', $dailyFuel->mileage ?? '') }}" readonly>
                  @if ($errors->has('mileage'))
                  <label class="text-danger">{{ $errors->first('mileage') }}</label>
                  @endif
                </div>
              </div>

              <!-- Fuel Taken -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Fuel Taken</strong>
                  <input type="number" min="0" step="0.01" class="form-control" name="fuel_taken" value="{{ old('fuel_taken', $dailyFuel->fuel_taken ?? '') }}">
                  @if ($errors->has('fuel_taken'))
                  <label class="text-danger">{{ $errors->first('fuel_taken') }}</label>
                  @endif
                </div>
              </div>


            </div>

            <div class="row">
              <!-- Fuel Avg. -->
              <div class="col-md-2">
                <div class="form-group">
                  <strong>Fuel Avg.</strong>
                  <input type="number" min="0" step="0.1" class="form-control" name="fuel_average" value="{{ old('fuel_average', $dailyFuel->fuel_average ?? '') }}" readonly>
                  @if ($errors->has('fuel_average'))
                  <label class="text-danger">{{ $errors->first('fuel_average') }}</label>
                  @endif
                </div>
              </div>


              <div class="col-md-10 mt-3">
                <div class="text-right">
                  <button type="submit" class="btn btn-primary">Save</button>
                  <a href="{{ route('admin.dailyFuels.index') }}" class="btn btn-warning">Cancel</a>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- jQuery + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // If there is a select to init (kept from template)
    if ($('#vehicle_id').length) {
      $('#vehicle_id').select2({
        placeholder: "--Select--",
        allowClear: true,
        theme: 'bootstrap4'
      });
    }

    function recalc() {
      var prev = parseFloat($('input[name="previous_km"]').val()) || 0;
      var curr = parseFloat($('input[name="current_km"]').val()) || 0;
      var mileage = curr - prev;
      if (mileage < 0) mileage = 0;
      $('input[name="mileage"]').val(mileage.toFixed(0));

      var fuel = parseFloat($('input[name="fuel_taken"]').val());
      var avg = 0;
      if (!isNaN(fuel) && fuel > 0) {
        avg = mileage / fuel;
      }
      $('input[name="fuel_average"]').val(avg.toFixed(1));
    }

    // Initialize on load
    recalc();

    // Recalculate when current_km or fuel_taken changes
    $(document).on('input', 'input[name="current_km"], input[name="fuel_taken"]', function(){
      recalc();
    });
  });
</script>
@endsection