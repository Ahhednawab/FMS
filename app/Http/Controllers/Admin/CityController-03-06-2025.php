<?php
namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('country')->get();
        return view('admin.cities.list', compact('cities'));
    }

    public function create()
    {
        $countries = Country::pluck('name', 'id');
        return view('admin.cities.form', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        City::create($request->all());

        return redirect()->route('admin.cities.index')->with('success', 'City created successfully.');
    }

    public function show(City $city)
    {
        return view('admin.cities.details', compact('city'));
    }

    public function edit(City $city)
    {
        $countries = Country::pluck('name', 'id');
        return view('admin.cities.form', compact('city', 'countries'));
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        $city->update($request->all());

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', 'City deleted successfully.');
    }
}
