<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyFuelMileage;
use App\Models\Vehicle;
use App\Models\Destination;
use App\Models\FuelStation;

use Illuminate\Http\Request;

class DailyFuelMileageController extends Controller
{
    public function index(){
        $dailyFuelMileages = DailyFuelMileage::with(['vehicle','destination','fuelStation'])->where('is_active',1)->get();
        
        return view('admin.dailyFuelMileages.index', compact('dailyFuelMileages'));
    }

    public function create(){
        $serial_no = DailyFuelMileage::GetSerialNumber();
        $vehicles = Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id');
        $destinations = Destination::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $fuelStations = FuelStation::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.dailyFuelMileages.create',compact('serial_no','destinations','fuelStations','vehicles'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id'        =>  'required',
                'destination'       =>  'required',
                'date'              =>  'required',
                'current_reading'   =>  'required|numeric',
                'previous_reading'  =>  'required|numeric',
                'difference'        =>  'required|numeric',
                'fuel_taken'        =>  'required|numeric',
                'consumption'       =>  'required|numeric',
                'fuel_station'      =>  'required',
                'driver_name'       =>  'required',
                'location'          =>  'required',
            ],
            [
                'vehicle_id.required'       =>  'Vehicle No is required',
                'destination.required'      =>  'Destination is required',
                'date.required'             =>  'Date is required',
                'current_reading.required'  =>  'Current Reading is required',
                'previous_reading.required' =>  'Previous Reading is required',
                'difference.required'       =>  'Difference is required',
                'fuel_taken.required'       =>  'Fuel Taken is required',
                'consumption.required'      =>  'Consumption is required',
                'current_reading.numeric'  =>  'Current Reading must be a number.',
                'previous_reading.numeric' =>  'Previous Reading must be a number.',
                'difference.numeric'       =>  'Difference must be a number.',
                'fuel_taken.numeric'       =>  'Fuel Taken must be a number.',
                'consumption.numeric'      =>  'Consumption must be a number.',
                'fuel_station.required'     =>  'Fuel Station is required',
                'driver_name.required'      =>  'Driver Name is required',
                'location.required'         =>  'Location is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyFuelMileage = new DailyFuelMileage();
        $dailyFuelMileage->serial_no        = $request->serial_no;
        $dailyFuelMileage->vehicle_id       = $request->vehicle_id;
        $dailyFuelMileage->destination      = $request->destination;
        $dailyFuelMileage->date             = $request->date;
        $dailyFuelMileage->current_reading  = $request->current_reading;
        $dailyFuelMileage->previous_reading = $request->previous_reading;
        $dailyFuelMileage->difference       = $request->difference;
        $dailyFuelMileage->fuel_taken       = $request->fuel_taken;
        $dailyFuelMileage->consumption      = $request->consumption;
        $dailyFuelMileage->fuel_station     = $request->fuel_station;
        $dailyFuelMileage->driver_name      = $request->driver_name;
        $dailyFuelMileage->location         = $request->location;
        $dailyFuelMileage->save();

        return redirect()->route('admin.dailyFuelMileages.index')->with('success', 'Daily Fuel Mileage created successfully.');
    }

    public function edit(DailyFuelMileage $dailyFuelMileage)
    {
        $destinations = array(
            '1' =>  'Destination 1',
            '2' =>  'Destination 2',
        );
        $fuelStations = array(
            '1' =>  'Fuel Station 1',
            '2' =>  'Fuel Station 2',
        );
        return view('admin.dailyFuelMileages.edit',compact('dailyFuelMileage','destinations','fuelStations'));
    }

    public function update(Request $request, DailyFuelMileage $dailyFuelMileage)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_id'        =>  'required',
                'destination'       =>  'required',
                'date'              =>  'required',
                'current_reading'   =>  'required|numeric',
                'previous_reading'  =>  'required|numeric',
                'difference'        =>  'required|numeric',
                'fuel_taken'        =>  'required|numeric',
                'consumption'       =>  'required|numeric',
                'fuel_station'      =>  'required',
                'driver_name'       =>  'required',
                'location'          =>  'required',
            ],
            [
                'vehicle_id.required'       =>  'Vehicle No is required',
                'destination.required'      =>  'Destination is required',
                'date.required'             =>  'Date is required',
                'current_reading.required'  =>  'Current Reading is required',
                'previous_reading.required' =>  'Previous Reading is required',
                'difference.required'       =>  'Difference is required',
                'fuel_taken.required'       =>  'Fuel Taken is required',
                'consumption.required'      =>  'Consumption is required',
                'current_reading.numeric'  =>  'Current Reading must be a number.',
                'previous_reading.numeric' =>  'Previous Reading must be a number.',
                'difference.numeric'       =>  'Difference must be a number.',
                'fuel_taken.numeric'       =>  'Fuel Taken must be a number.',
                'consumption.numeric'      =>  'Consumption must be a number.',
                'fuel_station.required'     =>  'Fuel Station is required',
                'driver_name.required'      =>  'Driver Name is required',
                'location.required'         =>  'Location is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dailyFuelMileage->vehicle_id       = $request->vehicle_id;
        $dailyFuelMileage->destination      = $request->destination;
        $dailyFuelMileage->date             = $request->date;
        $dailyFuelMileage->current_reading  = $request->current_reading;
        $dailyFuelMileage->previous_reading = $request->previous_reading;
        $dailyFuelMileage->difference       = $request->difference;
        $dailyFuelMileage->fuel_taken       = $request->fuel_taken;
        $dailyFuelMileage->consumption      = $request->consumption;
        $dailyFuelMileage->fuel_station     = $request->fuel_station;
        $dailyFuelMileage->driver_name      = $request->driver_name;
        $dailyFuelMileage->location         = $request->location;
        $dailyFuelMileage->save();

        return redirect()->route('admin.dailyFuelMileages.index')->with('success', 'Daily Fuel Mileage updated successfully.');
    }

    public function show(DailyFuelMileage $dailyFuelMileage)
    {
        echo "<pre>";
        print_r($dailyFuelMileage);
        echo "</pre>";
        return;
        return view('admin.dailyFuelMileages.show', compact('dailyFuelMileage'));
    }

    public function destroy(DailyFuelMileage $dailyFuelMileage)
    {
        $dailyFuelMileage->is_active = 0;
        $dailyFuelMileage->save();
        
        return redirect()->route('admin.dailyFuelMileages.index')->with('delete_msg', 'Daily Fuel Mileage deleted successfully.');
    }
}
