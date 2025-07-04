<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehiclesAttendance;
use Illuminate\Http\Request;

class VehiclesAttendanceController extends Controller
{
    public function index(){
        $vehicleAttendances = '';
        return view('admin.vehicleAttendances.index', compact('vehicleAttendances'));
    }

    public function create(){
        $serial_no = '654412364';
        return view('admin.vehicleAttendances.create',compact('serial_no'));
    }

    public function store(Request $request)
    {


        return redirect()->route('admin.vehicleAttendances.index')->with('success', 'Vehicle Attendances created successfully.');
    }

    public function show()
    {
        $vehicleAttendances='';
        return view('admin.vehicleAttendances.show', compact('vehicleAttendances'));
    }

    public function destroy()
    {
        
        return redirect()->route('admin.vehicleAttendances.index')->with('delete_msg', 'Vehicle Attendances deleted successfully.');
    }
}
