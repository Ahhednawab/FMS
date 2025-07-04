@extends('layouts.admin')

@section('title', 'Client Invoice Detail')

@section('content')
  <!-- Page header -->
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
        <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Client Invoice Detail</span></h4>
        <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.clientInvoices.index') }}" class="btn btn-primary"><span>View Client Invoice <i class="icon-list ml-2"></i></span></a>
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
                                    <h5 class="m-0">Invoice ID  </h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Due Date</h5>
                                    <p>Name</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Client Email</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Shipping Address</h5>
                                    <p>Driver</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                               
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Payment Method</h5>
                                    <p>21/12/2024</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Product Name</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Product Price</h5>
                                    <p>On Duty</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Discount</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                               
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Sub Total</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Client Name</h5>
                                    <p>13364</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Invoice Date</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>


                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Invoice Status</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Billing Address</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Product Quantity</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Referance No</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Tax Rate</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Order Price</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                <div class="col-md-3 text-center">
                                    <div class="card">
                                    <h5 class="m-0">Client Name</h5>
                                    <p>Monday</p>
                                    </div>
                                </div>
                               
                            </div>
          <!-- /basic datatable -->
          <div class="col-md-12">
            <label for=""></label>
            <div class="text-right">
              <a href="#" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.clientInvoices.index') }}" class="btn btn-secondary">Back</a>
                <form action="{{ route('admin.clientInvoices.destroy', 1) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this Client Invoice?');">
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
