<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriversAttendance;
use App\Models\Driver;
use App\Models\DriverStatus;
use App\Models\AttendanceStatus;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\VehicleDriverReplacementLog;
use App\Models\VehiclePoolDriver;
use App\Services\VehicleDriverAssignmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriversAttendanceController extends Controller
{
    public function __construct(private VehicleDriverAssignmentService $vehicleDriverAssignmentService)
    {
        if (!auth()->user()->hasPermission('driver_attendances')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index(Request $request)
    {
        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : Carbon::now()->startOfMonth();
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : Carbon::now();

        $driverAttendances = DriversAttendance::with([
            'driver.shiftTiming',
            'driver.driverStatus',
            'attendanceStatus',
            'vehicle',
            'originalDriver',
            'replacementDriver',
        ])
            ->where('is_active', 1)
            ->whereBetween('date', [
                $fromDate->toDateString(),
                $toDate->toDateString(),
            ])
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.driverAttendances.index', compact('driverAttendances'));
    }

    public function create(Request $request)
    {

        $stations = Station::where('is_active', 1)->get();
        $driver_status = DriverStatus::whereIn('id', function ($q) {
            $q->select('driver_status_id')
                ->from('drivers')
                ->where('is_active', 1)
                ->whereNotNull('driver_status_id');
        })
            ->orderBy('name')
            ->pluck('name', 'id');




        $excludeStatuses = ['Under maintanance', 'Inspection'];

        $driver_attendance_status = AttendanceStatus::where('is_active', 1)
            ->whereNotIn('name', $excludeStatuses)
            ->orderBy('id')
            ->pluck('name', 'id');
        $drivers = Driver::with([
            'driverStatus',
            'shiftTiming',
            'vehicle.poolDrivers' => function ($query) {
                $query->where('drivers.is_active', 1)
                    ->where('drivers.is_available', 1)
                    ->where('drivers.driver_type', 'pool')
                    ->orderBy('drivers.full_name');
            },
        ])
            ->where('drivers.is_active', 1)
            ->where('drivers.driver_type', 'regular')

            ->when($request->driver_status_id, function ($q) use ($request) {
                $q->where('drivers.driver_status_id', $request->driver_status_id);
            })

            ->when($request->station_id, function ($q) use ($request) {
                $q->whereHas('vehicle', function ($query) use ($request) {
                    $query->where('station_id', $request->station_id);
                });
            })

            ->leftJoin('driver_status', 'driver_status.id', '=', 'drivers.driver_status_id')

            ->orderByRaw("
                CASE
                    WHEN driver_status.name = 'Left' THEN 1
                    ELSE 0
                END
            ")
            ->orderBy('drivers.full_name', 'ASC')
            ->select('drivers.*')
            ->get();

        $selected_driver_status_id = $request->driver_status_id ?? '';

        $poolDriverOptions = $drivers->mapWithKeys(function (Driver $driver) {
            $poolDrivers = optional($driver->vehicle)->poolDrivers
                ?->mapWithKeys(fn (Driver $poolDriver) => [$poolDriver->id => $poolDriver->full_name])
                ?? collect();

            return [$driver->id => $poolDrivers];
        });

        $replaceStatusId = $this->getReplaceStatusId();

        return view('admin.driverAttendances.create', compact('drivers', 'driver_status', 'driver_attendance_status', 'selected_driver_status_id', 'stations', 'poolDriverOptions', 'replaceStatusId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $request->input('date');
        $driverIds = $request->input('driver_id', []);
        $statuses = $request->input('status', []);
        $replacementDriverIds = $request->input('replacement_driver_id', []);
        $replaceStatusId = $this->getReplaceStatusId();

        $fieldErrors = [];
        $toInsert = [];

        foreach ($driverIds as $i => $driverId) {
            $statusId = $statuses[$i] ?? null;

            if (empty($statusId)) {
                continue;
            }

            $driverId = (int) $driverId;
            if ($driverId <= 0) {
                continue;
            }

            $driver = Driver::with(['vehicle.poolDrivers'])->find($driverId);
            if (! $driver) {
                continue;
            }

            $isReplacement = $replaceStatusId !== null && (int) $statusId === $replaceStatusId;
            $replacementDriverId = (int) ($replacementDriverIds[$i] ?? 0);
            $attendanceDriverId = $driverId;

            if ($isReplacement) {
                if ($replacementDriverId <= 0) {
                    $fieldErrors['replacement_driver_id.' . $i] = 'Replacement pool driver is required when attendance is Replace.';
                    continue;
                }

                if (! $driver->vehicle) {
                    $fieldErrors['replacement_driver_id.' . $i] = 'This driver does not have a vehicle assigned for replacement.';
                    continue;
                }

                $poolDriverIds = $driver->vehicle->poolDrivers->pluck('id')->map(fn ($id) => (int) $id)->all();
                if (! in_array($replacementDriverId, $poolDriverIds, true)) {
                    $fieldErrors['replacement_driver_id.' . $i] = 'Selected replacement driver is not assigned in the vehicle pool.';
                    continue;
                }

                $replacementDriver = $driver->vehicle->poolDrivers->firstWhere('id', $replacementDriverId);
                if (! $replacementDriver || $replacementDriver->driver_type !== 'pool' || ! $replacementDriver->is_active || ! $replacementDriver->is_available) {
                    $fieldErrors['replacement_driver_id.' . $i] = 'Selected replacement driver is not available.';
                    continue;
                }

                $attendanceDriverId = $replacementDriverId;
            }

            $exists = DriversAttendance::where('date', $date)
                ->where('driver_id', $attendanceDriverId)
                ->where('is_active', 1)
                ->exists();

            if ($exists) {
                $prettyDate = Carbon::parse($date)->format('d-M-Y');
                $fieldErrors[$isReplacement ? 'replacement_driver_id.' . $i : 'status.' . $i] =
                    'Attendance already marked for this driver on ' . $prettyDate . '.';
                continue;
            }

            $toInsert[] = [
                'driver_id' => $attendanceDriverId,
                'vehicle_id' => $driver->vehicle_id,
                'original_driver_id' => $isReplacement ? $driverId : null,
                'replacement_driver_id' => $isReplacement ? $replacementDriverId : null,
                'is_replacement' => $isReplacement,
                'date' => $date,
                'status' => $statusId,
            ];
        }

        if (!empty($fieldErrors)) {
            return back()
                ->withInput()
                ->withErrors($fieldErrors);
        }

        DB::transaction(function () use ($toInsert, $date) {
            $changedDriverIds = [];

            foreach ($toInsert as $row) {
                $attendance = DriversAttendance::create($row);
                $changedDriverIds[] = (int) $attendance->driver_id;
                if ($attendance->original_driver_id) {
                    $changedDriverIds[] = (int) $attendance->original_driver_id;
                }
                if ($attendance->replacement_driver_id) {
                    $changedDriverIds[] = (int) $attendance->replacement_driver_id;
                }
                $this->syncDriverAvailability($attendance->driver_id, (int) $attendance->status);
            }

            $this->syncReplacementAssignmentsForDate($date, $changedDriverIds);
        });

        return redirect()->route('driverAttendances.index')->with('success', 'Driver Attendance marked successfully');
    }

    public function edit(DriversAttendance $driverAttendance)
    {
        $driver_attendance_status = AttendanceStatus::where('is_active', 1)->orderBy('id')->pluck('name', 'id');
        $replaceStatusId = $this->getReplaceStatusId();

        $driverAttendance->load([
            'driver.shiftTiming',
            'driver.driverStatus',
            'attendanceStatus',
            'vehicle',
            'originalDriver',
            'replacementDriver',
            'vehicle.poolDrivers' => function ($query) {
                $query->where('drivers.is_active', 1)
                    ->where('drivers.is_available', 1)
                    ->orderBy('drivers.full_name');
            },
        ]);

        $poolDriverOptions = optional($driverAttendance->vehicle)->poolDrivers
            ?->mapWithKeys(fn (Driver $poolDriver) => [$poolDriver->id => $poolDriver->full_name])
            ?? collect();

        return view('admin.driverAttendances.edit', compact('driverAttendance', 'driver_attendance_status', 'poolDriverOptions', 'replaceStatusId'));
    }

    public function update(Request $request, DriversAttendance $driverAttendance)
    {
        $validated = $request->validate([
            'date'   => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'exists:attendance_status,id'],
            'replacement_driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
        ]);

        $date = $validated['date'];
        $status = (int) $validated['status'];
        $replacementDriverId = (int) ($validated['replacement_driver_id'] ?? 0);
        $replaceStatusId = $this->getReplaceStatusId();
        $isReplacement = $replaceStatusId !== null && $status === $replaceStatusId;

        $mainDriver = $driverAttendance->originalDriver ?? $driverAttendance->driver;
        $vehicle = $driverAttendance->vehicle ?? $mainDriver?->vehicle;

        if ($isReplacement) {
            if ($replacementDriverId <= 0) {
                return back()
                    ->withInput()
                    ->withErrors(['replacement_driver_id' => 'Replacement pool driver is required when attendance is Replace.']);
            }

            if (! $vehicle) {
                return back()
                    ->withInput()
                    ->withErrors(['replacement_driver_id' => 'Vehicle is required for a replacement attendance.']);
            }

            $vehicle->loadMissing(['poolDrivers' => function ($query) {
                $query->where('drivers.is_active', 1)
                    ->where('drivers.driver_type', 'pool')
                    ->where('drivers.is_available', 1);
            }]);

            $poolDriverIds = $vehicle->poolDrivers->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array($replacementDriverId, $poolDriverIds, true)) {
                return back()
                    ->withInput()
                    ->withErrors(['replacement_driver_id' => 'Selected replacement driver is not assigned in the vehicle pool.']);
            }
        } else {
            $replacementDriverId = 0;
        }

        $attendanceDriverId = $isReplacement ? $replacementDriverId : (int) $mainDriver->id;

        $exists = DriversAttendance::where('driver_id', $attendanceDriverId)
            ->where('date', $date)
            ->where('is_active', 1)
            ->where('id', '!=', $driverAttendance->id)
            ->exists();

        if ($exists) {
            $prettyDate = Carbon::parse($date)->format('d-M-Y');
            return back()
                ->withInput()
                ->withErrors(['status' => 'Attendance already marked for this driver on ' . $prettyDate . '.']);
        }

        $originalDate = optional($driverAttendance->date)->toDateString() ?? (string) $driverAttendance->date;

        DB::transaction(function () use ($driverAttendance, $date, $status, $originalDate, $attendanceDriverId, $vehicle, $isReplacement, $mainDriver, $replacementDriverId) {
            $driverAttendance->update([
                'date'   => $date,
                'status' => $status,
                'driver_id' => $attendanceDriverId,
                'vehicle_id' => $vehicle?->id,
                'original_driver_id' => $isReplacement ? $mainDriver->id : null,
                'replacement_driver_id' => $isReplacement ? $replacementDriverId : null,
                'is_replacement' => $isReplacement,
            ]);

            $changedDriverIds = collect([
                $mainDriver?->id,
                $driverAttendance->getOriginal('driver_id'),
                $attendanceDriverId,
                $replacementDriverId > 0 ? $replacementDriverId : null,
            ])->filter()->map(fn ($id) => (int) $id)->all();

            foreach (array_unique($changedDriverIds) as $changedDriverId) {
                $this->syncDriverAvailability($changedDriverId, (int) $status);
            }

            $this->syncReplacementAssignmentsForDate($date, $changedDriverIds);

            if ($originalDate && $originalDate !== $date) {
                $this->syncReplacementAssignmentsForDate($originalDate, $changedDriverIds);
            }
        });

        return redirect()->route('driverAttendances.index')
            ->with('success', 'Driver Attendance updated successfully');
    }

    public function show(DriversAttendance $driverAttendance)
    {
        $driverAttendance->load([
            'driver.shiftTiming',
            'driver.driverStatus',
            'attendanceStatus',
            'vehicle',
            'originalDriver',
            'replacementDriver',
        ]);
        return view('admin.driverAttendances.show', compact('driverAttendance'));
    }

    public function destroy(DriversAttendance $driverAttendance)
    {
        $date = optional($driverAttendance->date)->toDateString() ?? (string) $driverAttendance->date;
        $driverId = (int) $driverAttendance->driver_id;

        $driverAttendance->is_active = 0;
        $driverAttendance->save();
        $this->syncReplacementAssignmentsForDate($date, [$driverId]);
        return redirect()->route('driverAttendances.index')->with('delete_msg', 'Driver Attendance deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->ids;
        $records = DriversAttendance::whereIn('id', $ids)->get(['id', 'driver_id', 'original_driver_id', 'replacement_driver_id', 'date']);
        DriversAttendance::whereIn('id', $ids)->update(['is_active' => 0]);

        $records
            ->groupBy(fn ($record) => optional($record->date)->toDateString() ?? (string) $record->date)
            ->each(function ($items, $date) {
                $driverIds = $items->flatMap(function ($item) {
                    return [
                        $item->driver_id,
                        $item->original_driver_id,
                        $item->replacement_driver_id,
                    ];
                })->filter()->map(fn ($id) => (int) $id)->all();

                $this->syncReplacementAssignmentsForDate($date, $driverIds);
            });

        return response()->json(['success' => true]);
    }

    private function syncDriverAvailability(int $driverId, int $statusId): void
    {
        $driver = Driver::with(['primaryVehicles', 'poolVehicles'])->find($driverId);
        if (!$driver) {
            return;
        }

        $statusName = strtolower((string) AttendanceStatus::where('id', $statusId)->value('name'));
        $unavailableStatuses = ['absent', 'leave', 'off day', 'inspection', 'under maintenance', 'under maintanance'];
        $driver->is_available = !in_array($statusName, $unavailableStatuses, true);
        $driver->save();

        foreach ($driver->primaryVehicles as $vehicle) {
            $this->vehicleDriverAssignmentService->resolveCurrentDriver($vehicle);
        }

        foreach ($driver->poolVehicles as $vehicle) {
            $this->vehicleDriverAssignmentService->resolveCurrentDriver($vehicle);
        }
    }

    private function syncReplacementAssignmentsForDate(string $date, array $driverIds): void
    {
        $driverIds = collect($driverIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($driverIds->isEmpty()) {
            return;
        }

        $vehicleIds = Vehicle::query()
            ->whereIn('primary_driver_id', $driverIds)
            ->pluck('id')
            ->merge(
                VehiclePoolDriver::query()
                    ->whereIn('driver_id', $driverIds)
                    ->pluck('vehicle_id')
            )
            ->unique()
            ->values();

        if ($vehicleIds->isEmpty()) {
            return;
        }

        $vehicles = Vehicle::with(['primaryDriver', 'poolDrivers'])
            ->whereIn('id', $vehicleIds)
            ->get();

        foreach ($vehicles as $vehicle) {
            $this->syncVehicleReplacementAssignment($vehicle, $date);
        }
    }

    private function syncVehicleReplacementAssignment(Vehicle $vehicle, string $date): void
    {
        if (! $vehicle->primaryDriver) {
            return;
        }

        $date = Carbon::parse($date)->toDateString();

        $relevantDriverIds = collect([$vehicle->primary_driver_id])
            ->merge($vehicle->poolDrivers->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        $attendances = DriversAttendance::with('attendanceStatus')
            ->whereDate('date', $date)
            ->where('is_active', 1)
            ->whereIn('driver_id', $relevantDriverIds)
            ->get()
            ->keyBy('driver_id');

        foreach ($attendances as $attendance) {
            $attendance->forceFill([
                'vehicle_id' => null,
                'original_driver_id' => null,
                'replacement_driver_id' => null,
                'is_replacement' => false,
            ])->save();
        }

        $primaryAttendance = $attendances->get($vehicle->primary_driver_id);

        if ($primaryAttendance && ! $this->attendanceMeansUnavailable($primaryAttendance)) {
            $primaryAttendance->forceFill([
                'vehicle_id' => $vehicle->id,
                'original_driver_id' => $vehicle->primary_driver_id,
                'replacement_driver_id' => null,
                'is_replacement' => false,
            ])->save();

            VehicleDriverReplacementLog::where('vehicle_id', $vehicle->id)
                ->whereDate('date', $date)
                ->delete();

            return;
        }

        $explicitReplacementAttendance = $attendances
            ->first(function (DriversAttendance $attendance) use ($vehicle) {
                return $attendance->is_replacement
                    && (int) $attendance->original_driver_id === (int) $vehicle->primary_driver_id
                    && ! $this->attendanceMeansUnavailable($attendance);
            });

        $replacementAttendance = $explicitReplacementAttendance ?: $vehicle->poolDrivers
            ->sortBy('id')
            ->map(fn ($poolDriver) => $attendances->get($poolDriver->id))
            ->first(fn ($attendance) => $attendance && ! $this->attendanceMeansUnavailable($attendance));

        if (! $replacementAttendance) {
            VehicleDriverReplacementLog::where('vehicle_id', $vehicle->id)
                ->whereDate('date', $date)
                ->delete();

            return;
        }

        $replacementAttendance->forceFill([
            'vehicle_id' => $vehicle->id,
            'original_driver_id' => $vehicle->primary_driver_id,
            'replacement_driver_id' => $replacementAttendance->driver_id,
            'is_replacement' => true,
        ])->save();

        VehicleDriverReplacementLog::updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'date' => $date,
            ],
            [
                'main_driver_id' => $vehicle->primary_driver_id,
                'replacement_driver_id' => $replacementAttendance->driver_id,
                'drivers_attendance_id' => $replacementAttendance->id,
                'notes' => 'Attendance captured against replacement pool driver.',
            ]
        );
    }

    private function attendanceMeansUnavailable(DriversAttendance $attendance): bool
    {
        $statusName = strtolower(trim((string) optional($attendance->attendanceStatus)->name));

        return in_array($statusName, [
            'absent',
            'leave',
            'off day',
            'inspection',
            'under maintenance',
            'under maintanance',
        ], true);
    }

    private function getReplaceStatusId(): ?int
    {
        $statusId = AttendanceStatus::query()
            ->where('is_active', 1)
            ->whereRaw('LOWER(name) = ?', ['replace'])
            ->value('id');

        return $statusId ? (int) $statusId : null;
    }
}
