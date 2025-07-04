<?php
namespace App\Http\Controllers\Admin;

use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::where('is_active',1)->get();
        return view('admin.cities.index', compact('cities'));
    }

    public function create()
    {
        $serial_no = City::GetSerialNumber();
        return view('admin.cities.create', compact('serial_no'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'      => 'required|string|max:255',
            ],
            [
                'name.required'         =>  'City Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $city = new City();
        $city->serial_no = $request->serial_no;
        $city->name = $request->name;
        $city->save();

        return redirect()->route('admin.cities.index')->with('success', 'City created successfully.');
    }

    public function show(City $city)
    {
        return view('admin.cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        return view('admin.cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'      => 'required|string|max:255',
            ],
            [
                'name.required'         =>  'City Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $city->name = $request->name;
        $city->save();

        return redirect()->route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        $city->is_active = 0;
        $city->save();
        
        return redirect()->route('admin.cities.index')->with('delete_msg', 'City deleted successfully.');
    }
}
