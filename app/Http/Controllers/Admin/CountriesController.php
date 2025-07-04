<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function index()
    {
        $countries = Country::where('is_active',1)->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function create()
    {
        $serial_no = Country::GetSerialNumber();
        return view('admin.countries.create', compact('serial_no'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [ 'name' => 'required|string|max:255' ],
            [ 'name.required'    =>  'Country Name is required']
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $country = new Country();
        $country->serial_no = $request->serial_no;
        $country->name = $request->name;
        $country->save();

        return redirect()->route('admin.countries.index')->with('success', 'Country created successfully.');
    }

    public function show(Country $country)
    {
        return view('admin.countries.show', compact('country'));
    }

    public function edit(Country $country)
    {
        return view('admin.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $validator = \Validator::make(
            $request->all(),
            [ 'name' => 'required|string|max:255' ],
            [ 'name.required'    =>  'Country Name is required']
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $country->name = $request->name;
        $country->save();

        return redirect()->route('admin.countries.index')->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country)
    {
        $country->is_active = 0;
        $country->save();

        return redirect()->route('admin.countries.index')->with('success', 'Country deleted successfully.');
    }
}
