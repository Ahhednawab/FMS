<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriversAttendance;
use App\Models\Driver;
use App\Models\DriverStatus;
use App\Models\AttendanceStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DriversAttendanceController extends Controller
{
    public function index(){
        $driverAttendances = DriversAttendance::with(['driver.shiftTiming','driver.driverStatus','driver','attendanceStatus'])
            ->where('is_active',1)
            ->orderBy('id','DESC')
            ->get();
        return view('admin.driverAttendances.index', compact('driverAttendances'));
    }

    public function create(Request $request){
        $driver_status = DriverStatus::whereIn('id', function ($q) {
                $q->select('driver_status_id')
                  ->from('drivers')
                  ->where('is_active', 1)
                  ->whereNotNull('driver_status_id');
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        $driver_attendance_status = AttendanceStatus::where('is_active', 1)->orderBy('id')->pluck('name', 'id');
        
        $drivers = Driver::with(['driverStatus','shiftTiming']);
        $drivers = $drivers->where('is_active', 1);
        if (isset($request->driver_status_id)) {
            $drivers = $drivers->where('driver_status_id', $request->driver_status_id);
        }
        $drivers = $drivers->orderBy(
                DriverStatus::select('name')
                    ->whereColumn('driver_status.id', 'drivers.driver_status_id') // 👈 table name fix
                    ->limit(1)
            );
        $drivers = $drivers->orderBy('full_name', 'ASC');
        $drivers = $drivers->get();

        $selected_driver_status_id = $request->driver_status_id ?? '';

        return view('admin.driverAttendances.create',compact('drivers','driver_status','driver_attendance_status','selected_driver_status_id'));
    }

    public function store(Request $request){
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $request->input('date');
        $driverIds = $request->input('driver_id', []);
        $statuses = $request->input('status', []);

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

            $exists = DriversAttendance::where('date', $date)
                ->where('driver_id', $driverId)
                ->where('is_active',1)
                ->exists();

            if ($exists) {
                $prettyDate = Carbon::parse($date)->format('d-M-Y');
                $fieldErrors['status.' . $i] = 'Attendance already marked for this driver on ' . $prettyDate . '.';
                continue;
            }

            $toInsert[] = [
                'driver_id' => $driverId,
                'date'      => $date,
                'status'    => $statusId,
            ];
        }

        if (!empty($fieldErrors)) {
            return back()
                ->withInput()
                ->withErrors($fieldErrors);
        }

        foreach ($toInsert as $row) {
            DriversAttendance::create($row);
        }

        return redirect()->route('admin.driverAttendances.index')->with('success', 'Driver Attendance marked successfully');
    }

    public function edit(DriversAttendance $driverAttendance){ 
        $driver_attendance_status = AttendanceStatus::where('is_active', 1)->orderBy('id')->pluck('name', 'id');
        
        $driverAttendance->load(['driver.shiftTiming','driver','attendanceStatus']);

        return view('admin.driverAttendances.edit',compact('driverAttendance','driver_attendance_status'));
    }

    public function update(Request $request, DriversAttendance $driverAttendance){
        $validated = $request->validate([
            'date'   => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'exists:attendance_status,id'],
        ]);

        $date = $validated['date'];
        $status = $validated['status'];

        $exists = DriversAttendance::where('driver_id', $driverAttendance->driver_id)
            ->where('date', $date)
            ->where('is_active',1)
            ->where('id', '!=', $driverAttendance->id)
            ->exists();

        if ($exists) {
            $prettyDate = Carbon::parse($date)->format('d-M-Y');
            return back()
                ->withInput()
                ->withErrors(['status' => 'Attendance already marked for this driver on ' . $prettyDate . '.']);
        }

        $driverAttendance->update([
            'date'   => $date,
            'status' => $status,
        ]);

        return redirect()->route('admin.driverAttendances.index')
            ->with('success', 'Driver Attendance updated successfully');
    }

    public function show(DriversAttendance $driverAttendance){
        $driverAttendance->load(['driver.shiftTiming','driver','attendanceStatus']);
        return view('admin.driverAttendances.show', compact('driverAttendance'));
    }

    public function destroy(DriversAttendance $driverAttendance){
        $driverAttendance->is_active = 0;
        $driverAttendance->save();
        return redirect()->route('admin.driverAttendances.index')->with('delete_msg', 'Driver Attendance deleted successfully.');
    }
}
