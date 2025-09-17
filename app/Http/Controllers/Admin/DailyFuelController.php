<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyFuelReport;
use App\Models\Vehicle;
use App\Models\Destination;
use App\Models\FuelStation;

use Illuminate\Http\Request;

class DailyFuelController extends Controller
{
    public function index(){
        $dailyFuels = DailyFuelReport::where('is_active', 1)
            ->whereHas('vehicle', function ($query) {
                $query->where('is_active', 1);
            })
            ->with('vehicle')
            ->get();

        //echo $dailyFuels;return;
        return view('admin.dailyFuels.index', compact('dailyFuels'));
    }

    public function create(){
        $serial_no = DailyFuelReport::GetSerialNumber();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        // $destinations = Destination::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        // $fuelStations = FuelStation::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.dailyFuels.create',compact('serial_no','vehicles'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id'        =>  'required',
                'date'              =>  'required',
                'current_km'   =>  'required|numeric',
                'fuel_taken'        =>  'required|numeric',
            ],
            [
                'vehicle_id.required'       =>  'Vehicle No is required',
                'date.required'             =>  'Date is required',
                'current_km.required'  =>  'Current Km is required',
                'fuel_taken.required'       =>  'Fuel Taken is required',
                'current_km.numeric'   =>  'Current Km must be a number.',
                'fuel_taken.numeric'        =>  'Fuel Taken must be a number.',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyFuel = new DailyFuelReport();
        $dailyFuel->serial_no        = $request->serial_no;
        $dailyFuel->vehicle_id       = $request->vehicle_id;
        $dailyFuel->date             = $request->date;
        $dailyFuel->current_km  = $request->current_km;
        $dailyFuel->fuel_taken       = $request->fuel_taken;
        $dailyFuel->save();

        return redirect()->route('admin.dailyFuels.index')->with('success', 'Daily Fuel created successfully.');
    }

    public function edit(DailyFuelReport $dailyFuel)
    {
        // $destinations = array(
        //     '1' =>  'Destination 1',
        //     '2' =>  'Destination 2',
        // );
        // $fuelStations = array(
        //     '1' =>  'Fuel Station 1',
        //     '2' =>  'Fuel Station 2',
        // );
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        return view('admin.dailyFuels.edit',compact('dailyFuel','vehicles'));
    }

    public function update(Request $request, DailyFuelReport $dailyFuel)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id'        =>  'required',
                'date'              =>  'required',
                'current_km'   =>  'required|numeric',
                'fuel_taken'        =>  'required|numeric'
            ],
            [
                'vehicle_id.required'       =>  'Vehicle No is required',
                'date.required'             =>  'Date is required',
                'current_km.required'  =>  'Current Km is required',
                'fuel_taken.required'       =>  'Fuel Taken is required',
                'current_km.numeric'  =>  'Current Km must be a number.',
                'fuel_taken.numeric'       =>  'Fuel Taken must be a number.'
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyFuel->vehicle_id       = $request->vehicle_id;
        $dailyFuel->date             = $request->date;
        $dailyFuel->current_km  = $request->current_km;
        $dailyFuel->fuel_taken       = $request->fuel_taken;
        $dailyFuel->save();

        return redirect()->route('admin.dailyFuels.index')->with('success', 'Daily Fuel updated successfully.');
    }

    public function show(DailyFuelReport $dailyFuel)
    {
        // echo "<pre>";
        // print_r($dailyFuel);
        // echo "</pre>";
        // return;
        return view('admin.dailyFuels.show', compact('dailyFuel'));
    }

    public function destroy(DailyFuelReport $dailyFuel)
    {
        $dailyFuel->is_active = 0;
        $dailyFuel->save();
        
        return redirect()->route('admin.dailyFuels.index')->with('delete_msg', 'Daily Fuel deleted successfully.');
    }
}
