<table border="1">
    <tr>
        <th colspan="5" style="font-size:16px;">Monthly Attendance Report</th>
    </tr>
    <tr>
        <th>Driver Name</th>
        <td>{{ $driver->full_name }}</td>
        <th>Vehicle</th>
        <td colspan="2">{{ $driver->vehicle?->vehicle_no ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>Month / Year</th>
        <td>{{ $monthLabel }}</td>
        <th>Total Working Days</th>
        <td>{{ $totalWorkingDays }}</td>
        <td></td>
    </tr>
    <tr>
        <th>Total Present</th>
        <td>{{ $presentDaysCount }}</td>
        <th>Total Absent</th>
        <td>{{ $absentDaysCount }}</td>
        <td></td>
    </tr>
</table>

<br>

<table border="1">
    <thead>
        <tr>
            <th>Date</th>
            <th>Day</th>
            <th>Vehicle</th>
            <th>Daily Attendance Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($calendarRows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                <td>{{ $row['day_name'] }}</td>
                <td>{{ $row['vehicle'] }}</td>
                <td>{{ $row['status_label'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
