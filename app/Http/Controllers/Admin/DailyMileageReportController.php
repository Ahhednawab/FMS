<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class DailyMileageReportController extends Controller
{
    public function index(Request $request)
    {
//        $query = DB::table('daily_mileage_report as d')
//            ->join('vehicles as v', 'd.vehicle_id', '=', 'v.id')
//            ->join('stations as s', 's.id', '=', 'v.station_id')
//            ->select('d.vehicle_id','v.vehicle_no', 's.area as station',
//                DB::raw('MIN(d.report_date) as start_date'),
//                DB::raw('MAX(d.report_date) as end_date'),
//                DB::raw('MIN(d.previous_km) AS start_km'),
//                DB::raw('MAX(d.current_km) as end_km'),
//                DB::raw('SUM(d.mileage) as total_mileage'))
//            ->where('d.is_active',1)->where('v.is_active',1);

        $query = DB::table('daily_mileage_report as d')
            ->join('vehicles as v', 'd.vehicle_id', '=', 'v.id')
            ->join('stations as s', 's.id', '=', 'v.station_id')
            ->select(
                'd.vehicle_id',
                'v.vehicle_no',
                's.area as station',
                DB::raw('MIN(d.report_date) as start_date'),
                DB::raw('MAX(d.report_date) as end_date'),
                DB::raw('MIN(d.previous_km) AS start_km'),
                DB::raw('MAX(d.current_km) as end_km'),
                DB::raw('SUM(d.mileage) as total_mileage')
            )
            ->where('d.is_active', 1)
            ->where('v.is_active', 1)
            ->groupBy('d.vehicle_id', 'v.vehicle_no', 's.area') // ✅ add s.area here
            ->orderBy('v.vehicle_no', 'asc');


        if ($request->filled('vehicle_id')) {
            $query->where('v.vehicle_no', $request->vehicle_id);

        }

        if ($request->filled('from_date')) {
            $query->whereDate('d.report_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('d.report_date', '<=', $request->to_date);
        }

        $dailyMileages = $query->groupby('d.vehicle_id', 'v.vehicle_no')->orderby('v.vehicle_no','ASC')->get();
        $vehicles = Vehicle::where('is_active',1)->get();

        return view('admin.dailyMileageReports.index', compact('dailyMileages','vehicles'));
    }

}
