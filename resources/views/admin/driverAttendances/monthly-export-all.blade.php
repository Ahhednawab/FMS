<table border="1" cellspacing="0" cellpadding="6">
    <tr>
        <th colspan="{{ 9 + $daysInMonth->count() }}" style="font-size:18px; font-weight:bold; text-align:center; background-color:#d9eaf7;">
            Monthly Attendance Register
        </th>
    </tr>
    <tr>
        <th colspan="{{ 9 + $daysInMonth->count() }}" style="text-align:center; font-weight:bold;">
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
            <th rowspan="2">Driver Name</th>
            <th colspan="{{ $daysInMonth->count() }}">Dates of {{ $monthLabel }}</th>
            <th rowspan="2">P</th>
            <th rowspan="2">A</th>
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
        @forelse ($driverSheets as $sheet)
            <tr>
                <td style="text-align:center;">{{ $sheet['serial_no'] }}</td>
                <td>{{ $sheet['station'] }}</td>
                <td>{{ $sheet['driver']->full_name }}</td>
                @foreach ($sheet['days'] as $day)
                    <td
                        @if($day['is_absent'])
                            style="text-align:center; background-color:#f8d7da; color:#721c24; font-weight:bold;"
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
                <td style="text-align:center; font-weight:bold;">{{ $sheet['off_days_count'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['total_present_days'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $sheet['total_days_in_month'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 9 + $daysInMonth->count() }}" style="text-align:center;">No monthly attendance records found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
