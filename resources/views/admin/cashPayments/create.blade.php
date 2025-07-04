@extends('layouts.admin')

@section('title', 'Add Cash Payment Voucher')

@section('content')
  <div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
      <div class="page-title d-flex">
          <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Cash Payment Voucher Management</span></h4>
      </div>
      <div class="header-elements d-none">
        <div class="d-flex justify-content-center">
          <a href="{{ route('admin.cashPayments.index') }}" class="btn btn-primary">
            <span>View Cash Payment Voucher <i class="icon-list ml-2"></i></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('admin.cashPayments.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          
          <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Voucher ID</label>
                          <input value="654412364" type="text" class="form-control" readonly>
                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Voucher Date</label>
                          <input type="date" class="form-control">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Payment Method</label>
                          <select class="custom-select">
                            <option value="1">Bank Transfer</option>
                            <option value="2">Cheque</option>
                            <option value="2">Pay Order</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Referance No</label>
                          <input type="text" class="form-control">

                        </div>
                      </div>
                      
                    </div>

                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <div class="form-group">
                            <label>Payment Status</label>
                            <select class="custom-select">
                              <option value="1">Pending</option>
                              <option value="2">Paid</option>
                              <option value="2">Approved</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Payee Name</label>
                          <input type="text" class="form-control">

                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Payee Type</label>
                          <select class="custom-select">
                            <option value="1">Vendor</option>
                            <option value="2">Supplier</option>
                            <option value="2">Employee</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Payee Contact</label>
                          <input type="text" class="form-control">
                        </div>
                      </div>
                      
                      
                    </div>

                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Payee Address</label>
                          <input type="text" class="form-control">
                        </div>
                      </div>
                      
                      

                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Amount</label>
                          <input type="text" class="form-control">
                        </div>
                      </div>



                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Tax Deduction</label>
                          <input type="text" class="form-control">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Total Amount</label>
                          <input type="text" class="form-control">
                        </div>
                      </div>  
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Description</label>
                          <!-- <input type="text" class="form-control"> -->
                          <textarea name="" id="" class="form-control"></textarea>
                        </div>
                      </div>
                    <div class="col-md-9">
                      <label for=""></label>
                      <div class="text-right">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('admin.cashPayments.index') }}" class="btn btn-warning">Cancel</a>
                      </div>
                    </div>
                    
        </form>
      </div>
    </div>
  </div>
@endsection
