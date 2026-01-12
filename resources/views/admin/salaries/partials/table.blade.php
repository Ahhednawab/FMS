@if ($drivers->isEmpty())
    <div class="alert alert-info">
        Please select a salary month to view and manage driver salaries.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" style="min-width: 2200px;">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>CNIC</th>
                    <th>Induction Date</th>
                    <th>Left Date</th>
                    <th>Employee Code</th>
                    <th>KE Card Serial</th>
                    <th>Location</th>
                    <th>Designation</th>
                    <th>Basic</th>
                    <th>Paid Absent</th>
                    <th>Overtime</th>
                    <th>Deduction</th>
                    <th>Extra</th>
                    <th>Advance Issued</th>
                    <th>Advance Deduction</th>
                    <th>Total Recovered</th>
                    <th>Remaining Amount</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Gross</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($drivers as $driver)
                    @php
                        $salary = $salaries[$driver->id] ?? null;
                        $isPaid = ($salary->status ?? 'pending') === 'paid';

                        $driverAdvances = $advances[$driver->id] ?? collect();
                        $advanceIssued = $driver->advance->amount ?? $driverAdvances->sum('amount');
                        $remainingAdvance = $advanceIssued - $driverAdvances->sum('advance_deduction');
                    @endphp

                    <tr class="salary-row" data-driver-id="{{ $driver->id }}" data-advance-issued="{{ $advanceIssued }}">
                        <input type="hidden" name="drivers[{{ $driver->id }}][driver_id]" value="{{ $driver->id }}">

                        <td>{{ $driver->full_name }}</td>
                        <td>{{ $driver->cnic_no }}</td>
                        <td>{{ $driver->employment_date }}</td>
                        <td>{{ $driver->last_date ?? '-' }}</td>
                        <td>{{ $driver->employee_code ?? '-' }}</td>
                        <td>{{ $driver->ke_card_serial ?? '-' }}</td>
                        <td>{{ $driver->location ?? '-' }}</td>
                        <td>{{ $driver->designation ?? '-' }}</td>

                        {{-- Basic --}}
                        <td>
                            @if ($isPaid)
                                {{ number_format($driver->salary, 2) }}
                            @else
                                <input type="number" class="form-control basic"
                                    name="drivers[{{ $driver->id }}][basic]" value="{{ $driver->salary }}" readonly>
                            @endif
                        </td>

                        {{-- Paid Absent --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->paid_absent ?? 0 }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][paid_absent]"
                                class="form-control calc" value="{{ $salary->paid_absent ?? 0 }}"
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Overtime --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->overtime_amount ?? 0 }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][overtime]"
                                class="form-control calc" value="{{ $salary->overtime_amount ?? 0 }}"
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Deduction --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->deduction_amount ?? 0 }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][deduction]"
                                class="form-control calc" value="{{ $salary->deduction_amount ?? 0 }}"
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Extra --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->extra ?? 0 }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][extra]" class="form-control calc"
                                value="{{ $salary->extra ?? 0 }}" style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Advance Issued --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $advanceIssued }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][advance_issued]"
                                class="form-control calc" value="{{ $advanceIssued }}" readonly
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Advance Deduction --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->advance_deduction ?? 0 }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][advance_deduction]"
                                class="form-control calc" value="{{ $driver->advance->per_month_deduction ?? 0 }}"
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Total Recovered --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $driver?->advance?->amount - $driver?->advance?->remaining_amount }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][total_recovered]"
                                class="form-control calc"
                                data-old-recovered="{{ $salary->total_recovered ?? $advanceIssued - $remainingAdvance }}"
                                value="{{ $driver?->advance?->amount - $driver?->advance?->remaining_amount }}"
                                readonly style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Remaining Amount --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->remaining_amount ?? $remainingAdvance }}</span>
                            <input type="number" name="drivers[{{ $driver->id }}][remaining_amount]"
                                class="form-control" value="{{ $salary->remaining_amount ?? $remainingAdvance }}"
                                readonly style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Remarks --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->remarks ?? '' }}</span>
                            <input type="text" name="drivers[{{ $driver->id }}][remarks]" class="form-control"
                                value="{{ $salary->remarks ?? '' }}"
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                        {{-- Status --}}
                        <td>
                            <select name="drivers[{{ $driver->id }}][status]" class="form-control status">
                                <option value="pending" {{ !$isPaid ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $isPaid ? 'selected' : '' }}>Paid</option>
                            </select>
                        </td>

                        {{-- Gross --}}
                        <td>
                            <span class="display-text"
                                style="display: {{ $isPaid ? 'inline' : 'none' }}">{{ $salary->gross_salary ?? $driver->salary }}</span>
                            <input type="number" class="form-control gross border border-success"
                                name="drivers[{{ $driver->id }}][gross]" readonly
                                style="display: {{ $isPaid ? 'none' : 'block' }}">
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
    <div class="mt-2">
        {{ $drivers->withQueryString()->links() }}
    </div>
@endif
