<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with('country', 'city')->latest()->get();
        return view('admin.warehouses.list', compact('warehouses'));
    }

    public function create()
    {
        $countries = Country::get();
        $cities = City::get();
        return view('admin.warehouses.form', compact('countries', 'cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'location' => 'nullable|string|max:255',
        ]);

        $warehouse = Warehouse::create([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'location' => $request->location,
        ]);

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function show($id)
    {
        $warehouse = Warehouse::with('country', 'city')->findOrFail($id);
        return view('admin.warehouses.details', compact('warehouse'));
    }

    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $countries = Country::get();
        $cities = City::get();
        return view('admin.warehouses.form', compact('warehouse', 'countries', 'cities'));
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'location' => 'nullable|string|max:255',
        ]);

        $warehouse->update([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'location' => $request->location,
        ]);

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}
