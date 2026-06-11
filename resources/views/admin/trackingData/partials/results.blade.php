<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle</th>
                        <th>AKPL</th>
                        <th>Shift</th>
                        <th>Total Km in a Day</th>
                        <th>MIS Peak HRS</th>
                        <th>AMS</th>
                        <th>Parking</th>
                        <th>Total KMS</th>
                        <th>ODO KMS</th>
                        <th>Diff</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trackingData as $row)
                        <tr class="tracking-data-row" data-vehicle="{{ $row['vehicle_filter_key'] }}"
                            data-off-peak="{{ $row['off_peak'] }}" data-mis-peak="{{ $row['mis_peak_hrs'] }}"
                            data-ams="{{ $row['ams'] }}" data-parking="{{ $row['parking'] }}"
                            data-total-kms="{{ $row['total_kms'] }}" data-odo-kms="{{ $row['odo_kms'] }}"
                            data-diff="{{ $row['diff'] }}">
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['vehicle'] }}</td>
                            <td>{{ $row['akpl'] }}</td>
                            <td>{{ $row['shift'] }}</td>
                            <td>{{ number_format($row['off_peak'], 1) }}</td>
                            <td>{{ number_format($row['mis_peak_hrs'], 1) }}</td>
                            <td>{{ number_format($row['ams'], 1) }}</td>
                            <td>{{ number_format($row['parking'], 1) }}</td>
                            <td>{{ number_format($row['total_kms'], 1) }}</td>
                            <td>{{ number_format($row['odo_kms'], 1) }}</td>
                            <td>{{ number_format($row['diff'], 1) }}</td>
                        </tr>
                    @empty
                        <tr id="tracking-data-original-empty">
                            <td colspan="11" class="text-center text-muted py-4">
                                No data available.
                            </td>
                        </tr>
                    @endforelse
                    @if (!empty($trackingData))
                        <tr id="tracking-data-filter-empty" style="display: none;">
                            <td colspan="11" class="text-center text-muted py-4">
                                No records match the selected vehicle filter.
                            </td>
                        </tr>
                    @endif
                </tbody>
                @if (!empty($trackingData))
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="4" class="text-right">Total</td>
                            <td id="tracking-total-off-peak">0.0</td>
                            <td id="tracking-total-mis-peak">0.0</td>
                            <td id="tracking-total-ams">0.0</td>
                            <td id="tracking-total-parking">0.0</td>
                            <td id="tracking-total-total-kms">0.0</td>
                            <td id="tracking-total-odo-kms">0.0</td>
                            <td id="tracking-total-diff">0.0</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
