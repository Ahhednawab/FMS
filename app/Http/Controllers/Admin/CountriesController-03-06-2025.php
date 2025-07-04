<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function index()
    {
        $countries = Country::all();
        return view('admin.countries.list', compact('countries'));
    }

    public function create()
    {
        return view('admin.countries.form');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Country::create(['name' => $request->name]);
        return redirect()->route('admin.countries.index')->with('success', 'Country added.');
    }

    public function show(Country $country)
    {
        return view('admin.countries.details', compact('country'));
    }

    public function edit(Country $country)
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $country->update(['name' => $request->name]);
        return redirect()->route('admin.countries.edit')->with('success', 'Country updated.');
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return redirect()->route('admin.countries.index')->with('success', 'Country deleted.');
    }
}
