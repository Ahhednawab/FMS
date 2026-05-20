<table border="1">
    <tr>
        <th colspan="6" style="font-size:16px;">Monthly Vehicle Attendance Report</th>
    </tr>
    <tr>
        <th>Vehicle</th>
        <td>{{ $vehicle->vehicle_no }}</td>
        <th>Station</th>
        <td colspan="3">{{ $vehicle->station?->area ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th>Month / Year</th>
        <td>{{ $monthLabel }}</td>
        <th>Total Working Days</th>
        <td>{{ $totalWorkingDays }}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <th>Total Present</th>
        <td>{{ $presentDaysCount }}</td>
        <th>Total Absent</th>
        <td>{{ $absentDaysCount }}</td>
        <td colspan="2"></td>
    </tr>
</table>

<br>

<table border="1">
    <thead>
        <tr>
            <th>Date</th>
            <th>Day</th>
            <th>Station</th>
            <th>Shift</th>
            <th>Daily Attendance Status</th>
            <th>Replacement Vehicle</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($calendarRows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-M-Y') }}</td>
                <td>{{ $row['day_name'] }}</td>
                <td>{{ $row['station'] }}</td>
                <td>{{ $row['shift'] }}</td>
                <td>{{ $row['status_label'] }}</td>
                <td>{{ $row['is_replacement'] ? ($row['replacement_vehicle'] ?? 'N/A') : 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
