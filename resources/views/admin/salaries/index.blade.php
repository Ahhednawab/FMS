@extends('layouts.admin')

@section('title', 'Salary List')

@section('content')
    <!-- Page header -->
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4>
                    <i class="icon-arrow-left52 mr-2"></i>
                    <span class="font-weight-semibold">Salary Months</span>
                </h4>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('salaries.create') }}" class="btn btn-primary">
                        <span>Add New Salary <i class="icon-plus3 ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- /page header -->

    <div class="content">

        @if ($message = Session::get('success'))
            <div id="alert-message" class="alert alert-success alert-dismissible alert-dismissible-2">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table datatable-colvis-basic">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($months as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->month)->format('F Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('salaries.show', $row->month) }}" class="btn btn-sm btn-info">
                                        <i class="icon-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
