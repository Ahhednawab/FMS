<table border="1">
    <tr>
        <th colspan="6" style="font-size:18px;">Monthly Attendance Report - All Drivers</th>
    </tr>
    <tr>
        <th colspan="6">Month / Year: {{ $monthLabel }}</th>
    </tr>
</table>

<br>

@foreach ($driverSheets as $sheet)
    <table border="1">
        <tr>
            <th>Driver Name</th>
            <td>{{ $sheet['driver']->full_name }}</td>
            <th>Vehicle</th>
            <td>{{ $sheet['driver']->vehicle?->vehicle_no ?? 'N/A' }}</td>
            <th>Month / Year</th>
            <td>{{ $monthLabel }}</td>
        </tr>
        <tr>
            <th>Total Working Days</th>
            <td>{{ $sheet['driver']->total_working_days }}</td>
            <th>Total Present</th>
            <td>{{ $sheet['driver']->total_present }}</td>
            <th>Total Absent</th>
            <td>{{ $sheet['driver']->total_absent }}</td>
        </tr>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Vehicle</th>
                <th>Daily Attendance Status</th>
                <th>Replacement Info</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sheet['rows'] as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                    <td>{{ $row['day_name'] }}</td>
                    <td>{{ $row['vehicle'] }}</td>
                    <td>{{ $row['status_label'] }}</td>
                    <td>
                        @if ($row['is_replacement'])
                            Main: {{ $row['original_driver'] ?? 'N/A' }} | Pool: {{ $row['replacement_driver'] ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>
@endforeach
