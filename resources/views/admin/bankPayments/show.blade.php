@extends('layouts.admin')

@section('title', 'Bank Payment Voucher Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Bank Payment Voucher Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.bankPayments.index') }}" class="btn btn-primary"><span>View Bank Payment Voucher <i class="icon-list ml-2"></i></span></a>
        </div>
      </div>
    </div>
  </div>
  <!-- /page header -->

  <!-- Content area -->
  <div class="content">
    <div class="card">
      <div class="card-body">
        <div class="container mt-3">
          
          <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Voucher ID</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payment Method</h5>
                                    <p>Name</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payment Status</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payee Type</h5>
                                    <p>Driver</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                               
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payee Address</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>

                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Referance No</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>


                              
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Total Amount</h5>
                                    <p>On Duty</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payee Contact</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                               
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Accident Type</h5>
                                    <p>13364</p>
                                    </div>
                                </div>

                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Bank Name</h5>
                                    <p>13364</p>
                                    </div>
                                </div>

                           
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payee Name</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>

                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Bank Branch</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>


                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Description</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>



                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Amount</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>


                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Tax Deduction</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>

                              

                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Total Amount</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>

                            </div>
          <!-- /basic datatable -->
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="#" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.bankPayments.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.bankPayments.destroy', 1) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Bank Payment Voucher?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger">Delete</button>
                </form>
            </div>
            <br>
          </div>
          
          
        </div>
      </div>
    </div>
  </div>
  <!-- /content area -->
@endsection
