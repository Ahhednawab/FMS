<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalaryMonthExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStrictNullComparison
{
    protected $drivers;
    protected Carbon $salaryMonth;

    public function __construct($drivers, Carbon $salaryMonth)
    {
        $this->drivers = $drivers;
        $this->salaryMonth = $salaryMonth;
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Employee ID',
            'Employee Name',
            'CNIC',
            'Station',
            'Designation',
            'Salary Month',
            'Basic Salary',
            'Extra / Allowances',
            'Overtime',
            'Deduction',
            'Advance Deduction',
            'Total Days',
            'Present Days',
            'Absent Days',
            'Net Salary',
            'Payment Status',
            'Remarks',
        ];
    }

    public function collection()
    {
        $monthLabel = $this->salaryMonth->format('F Y');

        return collect($this->drivers)->values()->map(function ($driver, $index) use ($monthLabel) {
            $salary    = optional($driver->salaries->first());
            $basic     = (float) $driver->salary;
            $extra     = (float) ($salary->extra ?? 0);
            $overtime  = (float) ($salary->overtime_amount ?? 0);
            $deduction = (float) ($salary->deduction_amount ?? 0);
            $advance   = (float) ($salary->advance_deduction ?? 0);
            $net       = $basic + $extra + $overtime - $deduction - $advance;

            return [
                $index + 1,
                $driver->employee_code,
                $driver->full_name,
                $driver->cnic_no,
                optional($driver->station)->area,
                $driver->designation,
                $monthLabel,
                round($basic, 2),
                round($extra, 2),
                round($overtime, 2),
                round($deduction, 2),
                round($advance, 2),
                $driver->total_days ?? 0,
                $driver->present_days ?? 0,
                $driver->absent_days ?? 0,
                round($net, 2),
                ucfirst($salary->status ?? 'pending'),
                $salary->remarks ?? '',
            ];
        });
    }

    public function title(): string
    {
        return 'Salary ' . $this->salaryMonth->format('F Y');
    }
}
