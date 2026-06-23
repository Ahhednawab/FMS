<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehiclesAttendance;
use App\Models\Vehicle;
use App\Models\Station;
use App\Models\AttendanceStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VehiclesAttendanceController extends Controller
{
    public function __construct()
    {

        if (!auth()->user()->hasPermission('vehicle_attendances')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index(Request $request)
    {
        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : Carbon::now();
        $station_id = $request->station_id;

        $vehicleAttendances = VehiclesAttendance::where('is_active', 1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['attendanceStatus', 'vehicle.station', 'vehicle.ibcCenter', 'vehicle.shiftHours'])->orderby('id', 'DESC')
            ->with('vehicle');

        if ($request->filled('station_id')) {
            $vehicleAttendances = $vehicleAttendances->whereHas('vehicle.station', function ($q) use ($request) {
                $q->where('id', $request->station_id);
            });
        }

        if ($request->filled('vehicle_id')) {
            $vehicleAttendances = $vehicleAttendances->whereHas('vehicle', function ($q) use ($request) {
                $q->where('vehicle_id', $request->vehicle_id);
            });
        }

        $vehicleAttendances = $vehicleAttendances->whereBetween('date', [
            $fromDate->toDateString(),
            $toDate->toDateString(),
        ]);
        $vehicleAttendances = $vehicleAttendances->orderBy('id', 'DESC');
        $vehicleAttendances = $vehicleAttendances->get();

        $stations = Station::orderBy('area', 'asc')->get(); // get list for dropdown

        return view('admin.vehicleAttendances.index', compact('vehicleAttendances', 'stations'));
    }

    public function monthlyIndex(Request $request)
    {
        $monthDate = $this->resolveMonthDate($request->input('month', now()->format('Y-m')));
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();

        $attendanceStatuses = AttendanceStatus::where('is_active', 1)->get()
            ->keyBy(fn (AttendanceStatus $status) => strtolower((string) $status->name));

        $presentStatusId = optional($attendanceStatuses->get('present'))->id;
        $absentStatusId = optional($attendanceStatuses->get('absent'))->id;

        $vehicles = Vehicle::with(['station', 'shiftHours'])
            ->where('is_active', 1)
            ->whereHas('vehicleAttendances', function ($query) use ($monthStart, $monthEnd) {
                $query->where('is_active', 1)
                    ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
            })
            ->withCount([
                'vehicleAttendances as total_working_days' => function ($query) use ($monthStart, $monthEnd) {
                    $query->where('is_active', 1)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                },
                'vehicleAttendances as total_present' => function ($query) use ($monthStart, $monthEnd, $presentStatusId) {
                    $query->where('is_active', 1)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

                    if ($presentStatusId) {
                        $query->where('status', $presentStatusId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
                'vehicleAttendances as total_absent' => function ($query) use ($monthStart, $monthEnd, $absentStatusId) {
                    $query->where('is_active', 1)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

                    if ($absentStatusId) {
                        $query->where('status', $absentStatusId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
            ])
            ->orderBy('vehicle_no')
            ->get();

        return view('admin.vehicleAttendances.monthly-index', [
            'vehicles' => $vehicles,
            'selectedMonth' => $monthDate->format('Y-m'),
            'monthLabel' => $monthDate->format('F Y'),
        ]);
    }

    public function monthlyShow(Request $request, Vehicle $vehicle)
    {
        $monthDate = $this->resolveMonthDate($request->input('month', now()->format('Y-m')));
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();
        $availableStatuses = ['present', 'absent', 'off', 'replace', 'no_record'];
        $selectedStatuses = collect($request->input('statuses', $availableStatuses))
            ->map(fn ($status) => strtolower((string) $status))
            ->intersect($availableStatuses)
            ->values()
            ->all();

        $vehicle->load(['station', 'shiftHours']);

        $attendanceRecords = VehiclesAttendance::with(['attendanceStatus', 'pool'])
            ->where('vehicle_id', $vehicle->id)
            ->where('is_active', 1)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn (VehiclesAttendance $attendance) => Carbon::parse($attendance->date)->toDateString());

        $calendarRows = $this->buildMonthlyVehicleCalendarRows($vehicle, $monthStart, $monthEnd, $attendanceRecords);
        $filteredCalendarRows = $calendarRows->filter(fn (array $row) => in_array($row['status_key'], $selectedStatuses, true))->values();

        return view('admin.vehicleAttendances.monthly-show', [
            'vehicle' => $vehicle,
            'calendarRows' => $filteredCalendarRows,
            'selectedMonth' => $monthDate->format('Y-m'),
            'monthLabel' => $monthDate->format('F Y'),
            'selectedStatuses' => $selectedStatuses,
            'presentDaysCount' => $calendarRows->where('attendance_status_key', 'present')->count(),
            'absentDaysCount' => $calendarRows->where('attendance_status_key', 'absent')->count(),
            'totalWorkingDays' => $calendarRows->whereNotIn('attendance_status_key', ['no_record'])->count(),
        ]);
    }

    public function monthlyExport(Request $request, Vehicle $vehicle)
    {
        $monthDate = $this->resolveMonthDate($request->input('month', now()->format('Y-m')));
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();

        $vehicle->load(['station', 'shiftHours']);

        $attendanceRecords = VehiclesAttendance::with(['attendanceStatus', 'pool'])
            ->where('vehicle_id', $vehicle->id)
            ->where('is_active', 1)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(fn (VehiclesAttendance $attendance) => Carbon::parse($attendance->date)->toDateString());

        $calendarRows = $this->buildMonthlyVehicleCalendarRows($vehicle, $monthStart, $monthEnd, $attendanceRecords);
        $content = view('admin.vehicleAttendances.monthly-export', [
            'vehicle' => $vehicle,
            'calendarRows' => $calendarRows,
            'monthLabel' => $monthDate->format('F Y'),
            'totalWorkingDays' => $calendarRows->whereNotIn('attendance_status_key', ['no_record'])->count(),
            'presentDaysCount' => $calendarRows->where('attendance_status_key', 'present')->count(),
            'absentDaysCount' => $calendarRows->where('attendance_status_key', 'absent')->count(),
        ])->render();

        $fileName = 'monthly-vehicle-attendance-' . $vehicle->id . '-' . $monthDate->format('Y-m') . '.xls';

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportMonthlyRegister(Request $request)
    {
        $monthDate = $this->resolveMonthDate($request->input('month') ?: $request->input('from_date'));
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();

        $attendanceStatuses = AttendanceStatus::where('is_active', 1)->get()
            ->keyBy(fn (AttendanceStatus $status) => strtolower((string) $status->name));

        $presentStatusId = optional($attendanceStatuses->get('present'))->id;
        $absentStatusId = optional($attendanceStatuses->get('absent'))->id;

        $vehicles = Vehicle::with(['station', 'shiftHours'])
            ->where('is_active', 1)
            ->whereHas('vehicleAttendances', function ($query) use ($monthStart, $monthEnd) {
                $query->where('is_active', 1)
                    ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
            })
            ->withCount([
                'vehicleAttendances as total_working_days' => function ($query) use ($monthStart, $monthEnd) {
                    $query->where('is_active', 1)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                },
                'vehicleAttendances as total_present' => function ($query) use ($monthStart, $monthEnd, $presentStatusId) {
                    $query->where('is_active', 1)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

                    if ($presentStatusId) {
                        $query->where('status', $presentStatusId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
                'vehicleAttendances as total_absent' => function ($query) use ($monthStart, $monthEnd, $absentStatusId) {
                    $query->where('is_active', 1)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

                    if ($absentStatusId) {
                        $query->where('status', $absentStatusId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
            ])
            ->orderBy('vehicle_no')
            ->get();

        $attendanceRecords = VehiclesAttendance::with('attendanceStatus')
            ->where('is_active', 1)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereIn('vehicle_id', $vehicles->pluck('id'))
            ->orderBy('date')
            ->get()
            ->groupBy('vehicle_id')
            ->map(fn ($records) => $records->keyBy(fn (VehiclesAttendance $attendance) => Carbon::parse($attendance->date)->toDateString()));

        $daysInMonth = collect(range(1, $monthEnd->day))->map(function (int $day) use ($monthDate) {
            $date = $monthDate->copy()->day($day);

            return [
                'day_number' => $day,
                'date' => $date->toDateString(),
                'day_name' => $date->format('D'),
                'is_sunday' => $date->isSunday(),
            ];
        });

        $vehicleSheets = $vehicles->values()->map(function (Vehicle $vehicle, int $index) use ($attendanceRecords, $daysInMonth) {
            $rowsByDate = $attendanceRecords->get($vehicle->id, collect());
            $offDaysCount = $daysInMonth->where('is_sunday', true)->count();
            $presentCount = (int) $vehicle->total_present;
            $absentCount = (int) $vehicle->total_absent;
            $underMaintenanceCount = $rowsByDate->filter(function (VehiclesAttendance $attendance) {
                $statusKey = strtolower(trim((string) optional($attendance->attendanceStatus)->name));

                return in_array($statusKey, ['under maintenance', 'under maintanance'], true);
            })->count();
            $inspectionCount = $rowsByDate->filter(function (VehiclesAttendance $attendance) {
                $statusKey = strtolower(trim((string) optional($attendance->attendanceStatus)->name));

                return $statusKey === 'inspection';
            })->count();

            return [
                'serial_no' => $index + 1,
                'station' => $vehicle->station?->area ?? 'N/A',
                'vehicle_name' => $vehicle->vehicle_no,
                'shift' => $this->resolveVehicleShiftLabel($vehicle),
                'days' => $daysInMonth->map(function (array $dayMeta) use ($rowsByDate) {
                    $row = $rowsByDate->get($dayMeta['date']);
                    $statusKey = strtolower(trim((string) optional(optional($row)->attendanceStatus)->name));

                    if (in_array($statusKey, ['under maintenance', 'under maintanance'], true)) {
                        return array_merge($dayMeta, [
                            'code' => 'UM',
                            'is_absent' => false,
                            'is_under_maintenance' => true,
                            'is_inspection' => false,
                        ]);
                    }

                    if ($statusKey === 'inspection') {
                        return array_merge($dayMeta, [
                            'code' => 'IN',
                            'is_absent' => false,
                            'is_under_maintenance' => false,
                            'is_inspection' => true,
                        ]);
                    }

                    if ($dayMeta['is_sunday']) {
                        return array_merge($dayMeta, [
                            'code' => 'Off',
                            'is_absent' => false,
                            'is_under_maintenance' => false,
                            'is_inspection' => false,
                        ]);
                    }

                    return array_merge($dayMeta, [
                        'code' => match ($statusKey) {
                            'present' => 'P',
                            'absent' => 'A',
                            default => '',
                        },
                        'is_absent' => $statusKey === 'absent',
                        'is_under_maintenance' => false,
                        'is_inspection' => false,
                    ]);
                }),
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'under_maintenance_count' => $underMaintenanceCount,
                'inspection_count' => $inspectionCount,
                'off_days_count' => $offDaysCount,
                'total_present_days' => $presentCount + $offDaysCount,
                'total_days_in_month' => $daysInMonth->count(),
            ];
        });

        $content = view('admin.vehicleAttendances.monthly-export-all', [
            'vehicleSheets' => $vehicleSheets,
            'monthLabel' => $monthDate->format('F Y'),
            'daysInMonth' => $daysInMonth,
        ])->render();

        $fileName = 'monthly-vehicle-attendance-' . $monthDate->format('Y-m') . '.xls';

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function create(Request $request)
    {
        $excludeStatuses = ['OFF'];
        $attendanceStatus = AttendanceStatus::where('is_active', 1)->where('id', '!=', 3)->whereNotIn('name', $excludeStatuses)->orderBy('id')->pluck('name', 'id');
        $vehicles = Vehicle::with(['station', 'shiftHours', 'ibcCenter']);
        $poolvehicles = Vehicle::where('pool_vehicle', 1)->get();
        $vehicles = $vehicles->where('is_active', 1);
        $vehicles = $vehicles->orderBy(Station::select('area')->whereColumn('stations.id', 'vehicles.station_id')->limit(1));
        $vehicles = $vehicles->orderBy('vehicle_no');
        $vehicles = $vehicles->get();

        $vehicleData = array();

        foreach ($vehicles as $vehicle) {

            $vehicleData[] = array(
                'vehicle_id'    =>  $vehicle->id,
                'station_id'    =>  $vehicle->station_id,
                'station'       =>  $vehicle->station->area,
                'vehicle_no'    =>  $vehicle->vehicle_no,
                'shift'         =>  $vehicle->shiftHours->name,
                'make'          =>  $vehicle->make,
                'model'         =>  $vehicle->model,
                'ibcCenter'     =>  $vehicle->ibcCenter->name
            );
        }

        $stations = Vehicle::with('station');
        $stations = $stations->where('is_active', 1);
        $stations = $stations->get();
        $stations = $stations->pluck('station.area', 'station_id');
        $stations = $stations->sort();
        $stations = $stations->unique();
        $stations = $stations->toArray();

        $selectedStation = $request->station_id ?? '';

        return view('admin.vehicleAttendances.create', compact('vehicles', 'stations', 'selectedStation', 'vehicleData', 'attendanceStatus', 'poolvehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $date = $request->input('date');
        $vehicleIds = $request->input('vehicle_id', []);
        $statuses = $request->input('status', []);
        $poolIds = $request->input('pool_id', []);

        $allowedStatusIds = AttendanceStatus::where('is_active', 1)
            ->where('id', '!=', 3)
            ->pluck('id')
            ->toArray();

        $fieldErrors = [];
        $toInsert = [];
        $selectedCount = 0;
        $firstVehicleId = null;

        foreach ($vehicleIds as $vehicleId) {
            $vehicleId = (int) $vehicleId;
            if ($vehicleId <= 0) {
                continue;
            }
            if ($firstVehicleId === null) {
                $firstVehicleId = $vehicleId;
            }

            $statusId = $statuses[(string) $vehicleId] ?? null;
            $poolId = $poolIds[(string) $vehicleId] ?? null;

            if (empty($statusId)) {
                continue;
            }

            $selectedCount++;

            $vehicleIsActive = Vehicle::where('id', $vehicleId)
                ->where('is_active', 1)
                ->exists();
            if (!$vehicleIsActive) {
                $fieldErrors['status.' . $vehicleId] = 'Invalid or inactive vehicle selected.';
                continue;
            }

            if (!in_array((int) $statusId, $allowedStatusIds, true)) {
                $fieldErrors['status.' . $vehicleId] = 'Invalid attendance status selected.';
                continue;
            }

            $exists = VehiclesAttendance::where('date', $date)
                ->where('vehicle_id', $vehicleId)
                ->where('is_active', 1)
                ->exists();

            if ($exists) {
                $prettyDate = Carbon::parse($date)->format('d-M-Y');
                $fieldErrors['status.' . $vehicleId] = 'Attendance already marked for this vehicle on ' . $prettyDate . '.';
                continue;
            }

            $attendanceData = [
                'vehicle_id' => $vehicleId,
                'date'       => $date,
                'status'     => (int) $statusId,
            ];

            if ($poolId) { // If pool_id is provided, include it in the data
                $attendanceData['pool_id'] = $poolId;
            }

            $toInsert[] = $attendanceData;
        }

        if ($selectedCount === 0) {
            if ($firstVehicleId !== null) {
                $fieldErrors['status.' . $firstVehicleId] = 'Please select attendance for at least one vehicle.';
            } else {
                $fieldErrors['date'] = 'Please select attendance for at least one vehicle.';
            }
        }

        if (!empty($fieldErrors)) {
            return back()
                ->withInput()
                ->withErrors($fieldErrors);
        }

        foreach ($toInsert as $row) {
            VehiclesAttendance::create($row);
        }

        return redirect()->route('vehicleAttendances.index')->with('success', 'Vehicle Attendances created successfully.');
    }

    public function edit(VehiclesAttendance $vehicleAttendance)
    {
        $vehicleAttendance->load(['attendanceStatus', 'vehicle.station', 'vehicle.shiftHours']);

        $attendanceStatus = AttendanceStatus::where('is_active', 1)
            ->where('id', '!=', 3)
            ->orderBy('id')
            ->pluck('name', 'id');

        return view('admin.vehicleAttendances.edit', compact('vehicleAttendance', 'attendanceStatus'));
    }

    public function update(Request $request, VehiclesAttendance $vehicleAttendance)
    {
        $validated = $request->validate([
            'date'   => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'exists:attendance_status,id'],
        ]);

        $date = $validated['date'];
        $status = $validated['status'];

        $exists = VehiclesAttendance::where('vehicle_id', $vehicleAttendance->vehicle_id)
            ->where('date', $date)
            ->where('is_active', 1)
            ->where('id', '!=', $vehicleAttendance->id)
            ->exists();

        if ($exists) {
            $prettyDate = Carbon::parse($date)->format('d-M-Y');
            return back()
                ->withInput()
                ->withErrors(['status' => 'Attendance already marked for this Vehicle on ' . $prettyDate . '.']);
        }

        $vehicleAttendance->update([
            'date'   => $date,
            'status' => $status,
        ]);

        return redirect()->route('vehicleAttendances.index')
            ->with('success', 'Vehicle Attendance updated successfully');
    }

    public function show(VehiclesAttendance $vehicleAttendance)
    {
        $vehicleAttendance->load(['attendanceStatus', 'vehicle.station', 'vehicle.shiftHours']);
        return view('admin.vehicleAttendances.show', compact('vehicleAttendance'));
    }

    public function destroy(VehiclesAttendance $vehicleAttendance)
    {
        $vehicleAttendance->is_active = 0;
        $vehicleAttendance->save();

        return redirect()->route('vehicleAttendances.index')->with('delete_msg', 'Vehicle Attendances deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->ids;
        VehiclesAttendance::whereIn('id', $ids)->update(['is_active' => 0]);
        return response()->json(['success' => true]);
    }

    private function resolveMonthDate(?string $month): Carbon
    {
        if (! empty($month)) {
            try {
                if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                    return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                }

                return Carbon::parse($month)->startOfMonth();
            } catch (\Throwable) {
            }
        }

        return now()->startOfMonth();
    }

    private function resolveVehicleShiftLabel(Vehicle $vehicle): string
    {
        $hours = $vehicle->shiftHours?->hours;
        if ($hours !== null && $hours !== '') {
            return (string) ((int) $hours) . ' Hours';
        }

        return $vehicle->shiftHours?->name ?? 'N/A';
    }

    private function buildMonthlyVehicleCalendarRows(Vehicle $vehicle, Carbon $monthStart, Carbon $monthEnd, $attendanceRecords)
    {
        $calendarRows = collect();
        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $dateKey = $cursor->toDateString();
            $attendance = $attendanceRecords->get($dateKey);
            $attendanceStatusKey = strtolower(trim((string) optional(optional($attendance)->attendanceStatus)->name));
            $attendanceStatusKey = $attendanceStatusKey !== '' ? $attendanceStatusKey : 'no_record';

            $calendarRows->push([
                'date' => $dateKey,
                'day_name' => $cursor->format('l'),
                'station' => $vehicle->station?->area ?? 'N/A',
                'shift' => $this->resolveVehicleShiftLabel($vehicle),
                'status_label' => $attendance?->attendanceStatus?->name ?? 'No Record',
                'status_key' => $attendance?->pool ? 'replace' : $attendanceStatusKey,
                'attendance_status_key' => $attendanceStatusKey,
                'is_replacement' => (bool) $attendance?->pool,
                'replacement_vehicle' => $attendance?->pool?->vehicle_no,
            ]);

            $cursor->addDay();
        }

        return $calendarRows;
    }
}
