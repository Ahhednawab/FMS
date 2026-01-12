@extends('layouts.admin')

@section('title', 'Add Client Invoice')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Client Invoice Management</span>
                </h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('clientInvoices.index') }}" class="btn btn-primary">
                        <span>View Client Invoice <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('clientInvoices.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Invoice ID</label>
                                <input value="" type="text" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Invoice Date</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Due Date</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Client Name</label>
                                <input type="date" class="form-control">

                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="form-group">
                                    <label>Client Email</label>
                                    <input type="email" class="form-control">

                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Billing Address</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Shipping Address</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Invoice Status</label>
                                <select class="custom-select">
                                    <option value="1">Draft</option>
                                    <option value="2">Pending</option>
                                    <option value="2">Paid</option>
                                </select>
                            </div>
                        </div>


                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="custom-select">
                                    <option value="1">Bank Tranfer</option>
                                    <option value="2">Check</option>
                                    <option value="2">Pay Order</option>
                                    <option value="2">Cash</option>
                                    <option value="2">Advance</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Referance No</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>



                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Product Quantity</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Product Price</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Order Price</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Discount</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tax Rate</label>
                                <input type="text" class="form-control">

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sub Total</label>
                                <input type="text" class="form-control">

                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Client Name</label>
                                <input type="text" class="form-control">

                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for=""></label>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('clientInvoices.index') }}" class="btn btn-warning">Cancel</a>
                            </div>
                        </div>

                </form>
            </div>
        </div>
    </div>
@endsection
