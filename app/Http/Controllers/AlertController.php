<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Models\AlertVehicleStatus;
use App\Models\DailyMileageReport;

class AlertController extends Controller
{

    public function index()
    {
        $alerts = Alert::paginate(10);
        return view('alerts.index', compact('alerts'));
    }
    public function create()
    {
        return view('alerts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'threshold' => 'required|integer|min:0',
        ]);

        Alert::create($request->only('title', 'threshold'));


        $alerts = Alert::all();
        $vehicles = Vehicle::all();

        foreach ($alerts as $alert) {
            foreach ($vehicles as $vehicle) {

                $currentMileage = DailyMileageReport::where('vehicle_id', $vehicle->id)
                    ->orderByDesc('report_date')
                    ->value('current_km');


                AlertVehicleStatus::firstOrCreate(
                    [
                        'alert_id' => $alert->id,
                        'vehicle_id' => $vehicle->id,
                    ],
                    [
                        'last_mileage' => $currentMileage,
                    ]
                );
            }
        }

        return redirect()->route('alerts.index')->with('success', 'Alert created successfully.');
    }


    public function edit(Alert $alert)
    {
        return view('alerts.edit', compact('alert'));
    }

    public function update(Request $request, Alert $alert)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'threshold' => 'required|integer|min:0',
        ]);

        $alert->update($request->only('title', 'threshold'));

        return redirect()->route('alerts.index')->with('success', 'Alert updated successfully.');
    }

    public function destroy(Alert $alert)
    {
        $alert->delete();
        return redirect()->route('alerts.index')->with('success', 'Alert deleted successfully.');
    }
}
