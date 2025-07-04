<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriversAttendance;
use Illuminate\Http\Request;

class DriversAttendanceController extends Controller
{
    public function index(){
        $driverAttendances = '';
        return view('admin.driverAttendances.index', compact('driverAttendances'));
    }

    public function create(){
        $serial_no = '654412364';
        return view('admin.driverAttendances.create',compact('serial_no'));
    }

    public function store(Request $request)
    {


        return redirect()->route('admin.driverAttendances.index')->with('success', 'Driver Attendance created successfully.');
    }

    public function show()
    {
        $driverAttendances='';
        return view('admin.driverAttendances.show', compact('driverAttendances'));
    }

    public function destroy()
    {
        
        return redirect()->route('admin.driverAttendances.index')->with('delete_msg', 'Driver Attendance deleted successfully.');
    }
}
