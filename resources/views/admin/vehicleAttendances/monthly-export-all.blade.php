<table border="1" cellspacing="0" cellpadding="6">
    <tr>
        <th colspan="{{ 11 + $daysInMonth->count() }}" style="font-size:18px; font-weight:bold; text-align:center; background-color:#d9eaf7;">
            Monthly Vehicle Attendance Register
        </th>
    </tr>
    <tr>
        <th colspan="{{ 11 + $daysInMonth->count() }}" style="text-align:center; font-weight:bold;">
            Month / Year: {{ $monthLabel }}
        </th>
    </tr>
</table>

<br>

<table border="1" cellspacing="0" cellpadding="6">
    <thead>
        <tr style="background-color:#e9ecef; font-weight:bold; text-align:center;">
            <th rowspan="2">S.No</th>
            <th rowspan="2">Station</th>
            <th rowspan="2">Vehicle Name</th>
            <th rowspan="2">Shift Type (12 Hr / 24 Hr)</th>
            <th colspan="{{ $daysInMonth->count() }}">Dates of {{ $monthLabel }}</th>
            <th rowspan="2">P</th>
            <th rowspan="2">A</th>
            <th rowspan="2">Under Maintenance</th>
            <th rowspan="2">Inspection</th>
            <th rowspan="2">Off</th>
            <th rowspan="2">Total Present Days</th>
            <th rowspan="2">Total Days of the Month</th>
        </tr>
        <tr style="background-color:#f8f9fa; text-align:center;">
            @foreach ($daysInMonth as $day)
                <th @if($day['is_sunday']) style="background-color:#fff3cd;" @endif>
                    {{ $day['day_number'] }}<br>{{ $day['day_name'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($vehicleSheets as $sheet)
            <tr>
                <td style="text-align:center;">{{ $sheet['serial_no'] }}</td>
                <td>{{ $sheet['station'] }}</td>
                <td>{{ $sheet['vehicle_name'] }}</td>
                <td style="text-align:center;">{{ $sheet['shift'] }}</td>
                @foreach ($sheet['days'] as $day)
                    <td
                        @if($day['is_absent'])
                            style="text-align:center; background-color:#f8d7da; color:#721c24; font-weight:bold;"
                        @elseif($day['is_under_maintenance'] ?? false)
                            style="text-align:center; background-color:#ffe5b4; color:#7a3e00; font-weight:bold;"
                        @elseif($day['is_inspection'] ?? false)
                            style="text-align:center; background-color:#d1ecf1; color:#0c5460; font-weight:bold;"
                        @elseif($day['code'] === 'Off')
                            style="text-align:center; background-color:#fff3cd; font-weight:bold;"
                        @else
                            style="text-align:center;"
                        @endif
                    >
                        {{ $day['code'] }}
                    </td>
                @endforeach
                <td style="text-align:center; font-weight:bold; color:#155724;">{{ $sheet['present_count'] }}</td>
                <td style="text-align:center; font-weight:bold; color:#721c24;">{{ $sheet['absent_count'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['under_maintenance_count'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['inspection_count'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['off_days_count'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['total_present_days'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['total_days_in_month'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 11 + $daysInMonth->count() }}" style="text-align:center;">No monthly vehicle attendance records found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
