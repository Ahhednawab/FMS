<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    // Show all locations
    public function index()
    {
        $locations = Location::where('is_active',1)->latest()->get();
        return view('admin.locations.index', compact('locations'));
    }

    // Show create form
    public function create()
    {
        return view('admin.locations.form'); // adjust view name if necessary
    }

    // Store new location
    public function store(Request $request)
    {
        $request->validate([
            'serial_no' => 'required|unique:locations',
            'country' => 'required|string',
            'city' => 'required|string',
            'area' => 'required|string',
        ]);

        Location::create($request->all());

        return redirect()->route('admin.locations.index')->with('success', 'Location added successfully.');
    }

    // Show location detail
    public function show(Location $location)
    {
        return view('admin.locations.show', compact('location'));
    }

    // Show edit form
    public function edit(Location $location)
    {
        return view('admin.locations.form', compact('location'));
    }

    // Update location
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'country' => 'required|string',
            'city' => 'required|string',
            'area' => 'required|string',
        ]);

        $location->update($request->all());

        return redirect()->route('admin.locations.index')->with('success', 'Location updated successfully.');
    }

    // Delete location
    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('admin.locations.index')->with('success', 'Location deleted.');
    }
}
