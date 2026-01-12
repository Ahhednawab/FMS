@extends('layouts.admin')

@section('title', 'Salary Details')

@section('content')

    <div class="page-header page-header-light">
        <div class="page-header-content d-flex justify-content-between align-items-center">
            <h4>Salary Details - {{ \Carbon\Carbon::parse($salaryMonth)->format('F, Y') }}</h4>
            <a href="{{ route('salaries.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="content mt-3">
        <div class="card">
            <div class="card-body">

                {{-- FILTERS --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Status</label>
                        <select name="status" class="form-control" id="statusFilter">
                            <option value="">-- All Statuses --</option>
                            <option value="pending" {{ !empty($status) && $status == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="paid" {{ !empty($status) && $status == 'paid' ? 'selected' : '' }}>Paid
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Rows per page</label>
                        <select name="per_page" class="form-control" id="perPageFilter">
                            @foreach ([5, 10, 25, 50, 100] as $count)
                                <option value="{{ $count }}" {{ $count == $perPage ? 'selected' : '' }}>
                                    {{ $count }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($drivers->count())
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Driver</th>
                                <th>CNIC</th>
                                <th>Basic</th>
                                <th>Extra</th>
                                <th>Overtime</th>
                                <th>Deduction</th>
                                <th>Advance</th>
                                <th>Status</th>
                                <th>Gross</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($drivers as $driver)
                                @php
                                    // salaries relation was eager-loaded for the month; take first if exists
                                    $salary = optional($driver->salaries->first());
                                    $basic = $driver->salary; // driver's base salary column
$extra = $salary->extra ?? 0;
$overtime = $salary->overtime_amount ?? 0;
$deduction = $salary->deduction_amount ?? 0;
$advance = $salary->advance_deduction ?? 0;
$gross = $basic + $extra + $overtime - $deduction - $advance;
$rowStatus = $salary->status ?? 'pending';
                                @endphp
                                <tr>
                                    <td>{{ ($drivers->currentPage() - 1) * $drivers->perPage() + $loop->iteration }}</td>
                                    <td>{{ $driver->full_name }}</td>
                                    <td>{{ $driver->cnic_no }}</td>
                                    <td>{{ number_format($basic, 2) }}</td>
                                    <td>{{ number_format($extra, 2) }}</td>
                                    <td>{{ number_format($overtime, 2) }}</td>
                                    <td>{{ number_format($deduction, 2) }}</td>
                                    <td>{{ number_format($advance, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $rowStatus === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($rowStatus) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($gross, 2) }}</td>
                                    <td>{{ $salary->remarks ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $drivers->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        No salaries found for this month with the selected filters.
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#statusFilter, #perPageFilter').on('change', function() {
                let status = $('#statusFilter').val();
                let perPage = $('#perPageFilter').val();
                let url = new URL(window.location.href);

                if (status) {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }

                url.searchParams.set('per_page', perPage);
                url.searchParams.set('page', 1);

                window.location.href = url.toString();
            });
        });
    </script>
@endpush
