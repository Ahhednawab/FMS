<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>RegNo</th>
                        <th>PeakKMs</th>
                        <th>OffPeakKMs</th>
                        <th>AMSKMs</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trackingData as $row)
                        <tr>
                            <td>{{ $row['RegNo'] }}</td>
                            <td>{{ $row['PeakKMs'] }}</td>
                            <td>{{ $row['OffPeakKMs'] }}</td>
                            <td>{{ $row['AMSKMs'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
