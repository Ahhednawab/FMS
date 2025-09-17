<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;

class DailyFuelReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('daily_fuel_reports as d')
            ->join('vehicles as v', 'd.vehicle_id', '=', 'v.id')
            ->select(
                'd.vehicle_id',
                'v.vehicle_no',
                DB::raw('MIN(d.date) as start_date'),
                DB::raw('MAX(d.date) as end_date'),
                DB::raw('Round(SUM(d.fuel_taken),2) as fuel_taken')
            )
            ->where('d.is_active', 1)->where('v.is_active', 1)
            ->whereRaw('MONTH(d.date) = MONTH(CURRENT_DATE())')
            ->whereRaw('YEAR(d.date) = YEAR(CURRENT_DATE())');

        if ($request->filled('vehicle_id')) {
            $query->where('v.vehicle_no', $request->vehicle_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('d.date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('d.date', '<=', $request->to_date);
        }

        $dailyFuelReports = $query->groupby('d.vehicle_id', 'v.vehicle_no')->orderby('v.vehicle_no', 'ASC')->get();
        $vehicles = Vehicle::where('is_active', 1)->get();

        return view('admin.dailyFuelReports.index', compact('dailyFuelReports', 'vehicles'));
    }
}
