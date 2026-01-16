<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use Illuminate\Http\Request;
use App\Models\DailyMileageReport;
use App\Models\Vehicle;
use App\Models\MileageStatus;
use App\Models\Station;
use Carbon\Carbon;
use Nette\Utils\Json;

class DailyMileageController extends Controller
{
    public function __construct()
    {

        if (!auth()->user()->hasPermission('daily_mileages')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index(Request $request)
    {

        $query = DailyMileageReport::query();

        if ($request->filled('vehicle_id')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vehicle_id', $request->vehicle_id);
            });
        }

        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : Carbon::now();

        $query->whereBetween('report_date', [
            $fromDate->toDateString(),
            $toDate->toDateString(),
        ]);

        $dailyMileages = $query->where('is_active', 1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['vehicle.station'])->orderby('report_date', 'DESC')
            ->get();

        $vehicles = Vehicle::where('is_active', 1)->get();

        return view('admin.dailyMileages.index', compact('dailyMileages', 'vehicles'));
    }

    public function create(Request $request)
    {
        // Get draft data if available
        $draftData = null;
        if ($request->filled('draft_id')) {
            $draftData = Draft::find($request->draft_id);
        }

        // Initialize selectedStation as empty
        $selectedStation = $request->station_id ?? '';


        // If draftData is available, use the selected station from the draft
        if ($draftData) {
            $selectedStation = $draftData->data['selectedstations'] ?? $selectedStation;
        }

        // Get vehicles based on the selected station
        $vehicles = Vehicle::with('station')
            ->where('is_active', 1)
            ->when($selectedStation, function ($query) use ($selectedStation) {
                return $query->whereIn('station_id', $selectedStation);  // Filter vehicles by the selected station
            })
            ->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1))
            ->orderBy('vehicle_no')
            ->get();

        // Prepare vehicle data
        $vehicleData = [];
        foreach ($vehicles as $vehicle) {
            $previousRecord = DailyMileageReport::where('vehicle_id', $vehicle->id)
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->first();

            $previous_km = ($previousRecord) ? $previousRecord->current_km : $vehicle->kilometer;

            $vehicleData[] = [
                'vehicle_id' => $vehicle->id,
                'station' => $vehicle->station->area,
                'vehicle_no' => $vehicle->vehicle_no,
                'previous_km' => $previous_km
            ];
        }

        // Get all stations for the filter dropdown
        $stations = Vehicle::with('station')
            ->where('is_active', 1)
            ->get()
            ->pluck('station.area', 'station_id')
            ->sort()
            ->unique()
            ->toArray();

        // Return view with necessary data
        return view('admin.dailyMileages.create', compact('vehicles', 'vehicleData', 'stations', 'selectedStation', 'draftData'));
    }


    //    public function store(Request $request)
    //    {
    //        $validator = \Validator::make(
    //            $request->all(),
    //            [
    //                'current_km'    => 'array',
    //                'current_km.*'  => 'nullable|numeric|min:0',
    //                'report_date' => 'required|date|before_or_equal:today',
    //            ],
    //            [
    //                'report_date.required'        =>  'Date is required',
    //                'report_date.date'            =>  'Date must be a valid date',
    //                'report_date.before_or_equal' =>  'Date cannot be greater than today',
    //            ]
    //        );
    //
    //
    //        $validator->after(function ($validator) use ($request) {
    //            $previous_kms = $request->previous_km;
    //            $current_kms = $request->current_km;
    //            $vehicle_ids = $request->vehicle_id;
    //            $report_date = $request->report_date;
    //
    //            foreach ($current_kms as $index => $current_km){
    //                $previous_km = $previous_kms[$index] ?? 0;
    //
    //                if($current_km < $previous_km && isset($current_km)){
    //                    $validator->errors()->add("current_km.$index", "Current KM must be greater than Previous KM.");
    //                }
    //
    //                $vehicle_id = $vehicle_ids[$index] ?? null;
    //                if ($vehicle_id && isset($current_km)) {
    //                    $last = DailyMileageReport::where('vehicle_id', $vehicle_id)
    //                        ->where('is_active', 1)
    //                        ->orderBy('report_date', 'desc')
    //                        ->orderBy('id', 'desc')
    //                        ->first();
    //
    //                    if ($last) {
    //                        if ($report_date && Carbon::parse($report_date)->lt(Carbon::parse($last->report_date))) {
    //                            $lastDateFormatted = Carbon::parse($last->report_date)->format('d-M-Y');
    //                            $validator->errors()->add("current_km.$index", "Report date cannot be earlier than last date ($lastDateFormatted) for this vehicle.");
    //                        }
    //
    //                        if ($current_km < $last->current_km) {
    //                            $validator->errors()->add("current_km.$index", "Current KM for this vehicle cannot be less than last recorded KM ({$last->current_km}).");
    //                        }
    //                    }
    //                }
    //            }
    //        });
    //
    //        if ($validator->fails()) {
    //            $messages = $validator->getMessageBag();
    //            return redirect()->back()->withErrors($validator)->withInput();
    //        }
    //
    //        $vehicle_ids    = $request->vehicle_id;
    //        $report_date    = $request->report_date;
    //        $mileages       = $request->mileage;
    //        $previous_kms   = $request->previous_km;
    //        $current_kms    = $request->current_km;
    //
    //        foreach ($vehicle_ids as $index => $vehicle_id) {
    //            if (isset($current_kms[$index])) {
    //                DailyMileageReport::Create([
    //                    'vehicle_id'    =>  $vehicle_id,
    //                    'report_date'   =>  $report_date,
    //                    'mileage'       =>  ($current_kms[$index] - $previous_kms[$index]),
    //                    'previous_km'   =>  $previous_kms[$index],
    //                    'current_km'    =>  $current_kms[$index],
    //                ]);
    //            }
    //        }
    //
    //        return redirect()->route('admin.dailyMileages.index')->with('success', 'Daily Mileage created successfully.');
    //    }


    public function store(Request $request)
    {

        $request->validate([
            'report_date' => 'required|date|before_or_equal:today',
            'vehicles'    => 'required|array',
        ], [
            'report_date.required' => 'Date is required',
            'report_date.date' => 'Invalid date',
            'report_date.before_or_equal' => 'Date cannot be in the future',
        ]);


        $reportDate = Carbon::parse($request->report_date)->format('Y-m-d');
        $vehicles   = $request->vehicles;

        /* ================= VALIDATION ================= */
        foreach ($vehicles as $vehicleId => $data) {

            $previousKm = $data['previous_km'] ?? 0;
            $currentKm  = $data['current_km'] ?? null;
            if ($currentKm !== null && $currentKm < $previousKm) {
                return back()
                    ->withErrors(["vehicles.$vehicleId.current_km" =>
                    "Current KM must be greater than Previous KM"])
                    ->withInput();
            }

            if ($currentKm !== null) {
                $last = DailyMileageReport::where('vehicle_id', $vehicleId)
                    ->where('report_date', '<=', $reportDate)
                    ->orderBy('report_date', 'desc')
                    ->first();


                if ($last && $currentKm < $last->current_km) {
                    return back()
                        ->withErrors(["vehicles.$vehicleId.current_km" =>
                        "Current KM cannot be less than last recorded KM ({$last->current_km})"])
                        ->withInput();
                }
            }
        }

        /* ================= DRAFT CHECK ================= */
        $isDraft = collect($vehicles)->contains(fn($v) => empty($v['current_km']));

        if (true) {

            // Save / replace draft
            Draft::where('module', 'dailymileage')
                ->where('created_by', auth()->id())
                ->where('data->report_date', $reportDate)
                ->delete();

            Draft::create([
                'module' => 'dailymileage',
                'created_by' => auth()->id(),
                'data' => [
                    'report_date' => $reportDate,
                    'vehicles' => $vehicles
                ]
            ]);

            // ✅ Insert/update reports for vehicles that have current_km
            foreach ($vehicles as $vehicleId => $data) {

                if (empty($data['current_km'])) {
                    continue; // skip incomplete rows
                }

                $previousKm = $data['previous_km'] ?? 0;
                $currentKm  = $data['current_km'];
                $mileage    = max(0, $currentKm - $previousKm);

                DailyMileageReport::updateOrCreate(
                    [
                        'vehicle_id'  => $vehicleId,
                        'report_date' => $reportDate,
                    ],
                    [
                        'previous_km' => $previousKm,
                        'current_km'  => $currentKm,
                        'mileage'     => $mileage,
                        'is_active'   => 0, // mark as draft (optional)
                    ]
                );
            }

            return back()->with('success', 'Draft saved and partial mileage recorded.');
        }
    }



    public function edit(DailyMileageReport $dailyMileage)
    {
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');

        $months = [];

        for ($i = 0; $i <= 3; $i++) {
            $months[] = date("F", strtotime("-$i months"));
        }

        return view('admin.dailyMileages.edit', compact('dailyMileage', 'months', 'vehicles'));
    }

    public function update(Request $request, DailyMileageReport $dailyMileage)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date' => 'required',
                'current_km' => 'required|numeric',
            ],
            [
                'report_date.required'                 =>  'Date is required',
                'current_km.required'     =>  'Current km is required'
            ]
        );

        $validator->after(function ($validator) use ($request, $dailyMileage) {
            $previous_km = $request->previous_km;
            $current_km = $request->current_km;

            if ($current_km < $previous_km) {
                $validator->errors()->add("current_km", "Current KM must be greater than Previous KM.");
            }

            $exists = DailyMileageReport::where('vehicle_id', $dailyMileage->vehicle_id)
                ->where('is_active', 1)
                ->where('id', '!=', $dailyMileage->id)
                ->whereDate('report_date', $request->report_date)
                ->exists();

            if ($exists) {
                $validator->errors()->add('report_date', 'A report for this date already exists for this vehicle.');
            }
        });

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyMileage->report_date  = $request->report_date;
        $dailyMileage->mileage      = $request->mileage;
        $dailyMileage->previous_km  = $request->previous_km;
        $dailyMileage->current_km   = $request->current_km;
        $dailyMileage->save();

        $succeded_record = DailyMileageReport::where('vehicle_id', $dailyMileage->vehicle_id)->where('id', '>', $dailyMileage->id)->first();

        if (isset($succeded_record)) {
            $succeded_record->previous_km   =  $request->current_km;
            $succeded_record->mileage       = ($succeded_record->current_km - $request->current_km);
            $succeded_record->save();
        }

        return redirect()->route('dailyMileages.index')->with('success', 'Daily Mileage updated successfully.');
    }

    public function show(DailyMileageReport $dailyMileage)
    {
        return view('admin.dailyMileages.show', compact('dailyMileage'));
    }

    public function destroy(DailyMileageReport $dailyMileage)
    {
        $dailyMileage->is_active = 0;
        $dailyMileage->save();
        return redirect()->route('dailyMileages.index')->with('delete_msg', 'Daily Mileage deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->ids;
        DailyMileageReport::whereIn('id', $ids)->update(['is_active' => 0]);
        return response()->json(['success' => true]);
    }



    public function fetchDailyMilages(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date'
        ]);

        $reportDate = Carbon::parse($request->report_date)->format('Y-m-d');

        // 1️⃣ Get records for selected date
        $records = DailyMileageReport::with('vehicle')
            ->where('report_date', $reportDate)
            ->get()
            ->keyBy('vehicle_id');

        // 2️⃣ Get last previous entry BEFORE selected date (per vehicle)
        $previousRecords = DailyMileageReport::with('vehicle')
            ->where('report_date', '<', $reportDate)
            ->whereIn('id', function ($query) use ($reportDate) {
                $query->selectRaw('MAX(id)')
                    ->from('daily_mileage_report')
                    ->where('report_date', '<', $reportDate)
                    ->groupBy('vehicle_id');
            })
            ->get()
            ->keyBy('vehicle_id');

        // 3️⃣ Merge results
        $finalData = [];

        foreach ($records as $vehicleId => $record) {
            $finalData[$vehicleId] = $record;
        }

        foreach ($previousRecords as $vehicleId => $record) {
            if (!isset($finalData[$vehicleId])) {
                $finalData[$vehicleId] = [
                    'vehicle_id'  => $vehicleId,
                    'previous_km' => $record->current_km, // IMPORTANT
                    'current_km'  => null,
                    'mileage'     => null,
                    'vehicle'     => $record->vehicle,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => array_values($finalData),
            'message' => 'Data fetched successfully'
        ]);
    }
}
