@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-content">
    <!-- Page Header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Home</span> - Dashboard</h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <!-- Main Content -->
    <div class="content">
        <div class="row">
            <!-- Card 1 -->
            <div class="col-lg-4">
                <div class="card bg-teal text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0">3,450</h3>
                            <span class="badge badge-dark badge-pill align-self-center ml-auto">+53.6%</span>
                        </div>
                        <div>Total Vehicles</div>
                        <div class="font-size-sm opacity-75">489 avg</div>
                    </div>
                    <div class="container-fluid page-header-light py-3"><div id="members-online"></div></div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-lg-4">
                <div class="card bg-pink text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0">49.4%</h3>
                            <div class="list-icons ml-auto">
                                <a href="#" class="list-icons-item"><i class="icon-cog3"></i></a>
                            </div>
                        </div>
                        <div>Total Drivers</div>
                        <div class="font-size-sm opacity-75">34.6% avg</div>
                    </div>
                    <div id="server-load"></div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-lg-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <h3 class="font-weight-semibold mb-0">$18,390</h3>
                            <div class="list-icons ml-auto">
                                <a class="list-icons-item" data-action="reload"></a>
                            </div>
                        </div>
                        <div>This week maintenance</div>
                        <div class="font-size-sm opacity-75">$37,578 avg</div>
                    </div>
                    <div id="today-revenue"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Main Content -->
</div>
@endsection
