@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">{{ isset($user) ? 'Edit User' : 'Add User' }}</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
            <span>View Users <i class="icon-list ml-2"></i></span>
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
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
              @csrf
              @method('PUT')

              <div class="row">
                <!-- Serial No -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Serial No</strong>
                    <input type="text" name="serial_no" class="form-control" value="{{$user->serial_no}}" readonly>
                  </div>
                </div>

                <!-- Full Name -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Full Name</strong>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->name ?? '') }}">
                    @if ($errors->has('full_name'))
                      <label class="text-danger">{{ $errors->first('full_name') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Email</strong>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}">
                    @if ($errors->has('email'))
                      <label class="text-danger">{{ $errors->first('email') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Phone -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Phone</strong>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
                    @if ($errors->has('phone'))
                      <label class="text-danger">{{ $errors->first('phone') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Designation -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Designation</strong>
                    <select class="custom-select" name="designation_id">
                      <option value="">Select Designation</option>
                      @foreach($designation as $value)
                        <option value="{{$value->id}}" {{ old('designation_id', $user->designation_id ?? '') == $value->id ? 'selected' : '' }}>{{$value->designation}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('designation_id'))
                      <label class="text-danger">{{ $errors->first('designation_id') }}</label>
                    @endif
                  </div>
                </div>
              
                <!-- Password -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Password <small>(Leave blank to keep current)</small></strong>
                    <input type="password" name="password" class="form-control">
                    @if ($errors->has('password'))
                      <label class="text-danger">{{ $errors->first('password') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Confirm Password</strong>
                    <input type="password" name="password_confirmation" class="form-control">
                  </div>
                </div>

                <!-- Address -->
                <div class="col-md-3">
                  <div class="form-group">
                    <strong>Full Address</strong>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $user->address ?? '') }}">
                    @if ($errors->has('address'))
                      <label class="text-danger">{{ $errors->first('address') }}</label>
                    @endif
                  </div>
                </div>

                <!-- Buttons -->
                <div class="col-md-12">
                  <label for=""></label>
                  <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-warning">Cancel</a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#cnic_no').inputmask("99999-9999999-9");
      $('#phone').inputmask("0399-9999999");
    });
  </script>
@endsection