<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle</th>
                        <th>Peak</th>
                        <th>Off Peak</th>
                        <th>AMS</th>
                        <th>AKPL</th>
                        <th>Shift</th>
                        <th>Parking</th>
                        <th>Total KMs</th>
                        <th>ODO</th>
                        <th>Diff</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trackingData as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['veh_reg'] }}</td>
                            <td>{{ number_format($row['peak_kms_api'], 1) }}</td>
                            <td>{{ number_format($row['off_peak_kms_api'], 1) }}</td>
                            <td>{{ number_format($row['ams_kms_api'], 1) }}</td>
                            <td>{{ $row['akpl'] }}</td>
                            <td>{{ $row['shift'] }}</td>
                            <td>{{ number_format($row['parking'], 1) }}</td>
                            <td>{{ number_format($row['total_kms'], 1) }}</td>
                            <td>{{ number_format($row['odo_kms'], 1) }}</td>
                            <td>{{ number_format($row['diff'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                No data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($trackingData) > 0)
                    <tfoot>
                        <tr class="font-weight-semibold bg-light">
                            <td colspan="2">Totals</td>
                            <td>{{ number_format($totals['peak'], 1) }}</td>
                            <td>{{ number_format($totals['off_peak'], 1) }}</td>
                            <td>{{ number_format($totals['ams'], 1) }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($totals['parking'], 1) }}</td>
                            <td>{{ number_format($totals['total_kms'], 1) }}</td>
                            <td>{{ number_format($totals['odo_kms'], 1) }}</td>
                            <td>{{ number_format($totals['diff'], 1) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
