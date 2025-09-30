<?php
namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Draft;
use App\Traits\DraftTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    use DraftTrait;
    public function index()
    {
        $cities = City::where('is_active',1)->get();
        return view('admin.cities.index', compact('cities'));
    }

    public function create(Request $request)
    {
        $serial_no = City::GetSerialNumber();
        
        $draftData = $this->getDraftDataForView($request, 'cities');
        
        return view('admin.cities.create', compact('serial_no') + $draftData);
    }

    public function store(Request $request)
    {
        // Handle draft saving
        if ($this->handleDraftSave($request, 'cities')) {
            return redirect()->back()->with('success', 'Draft saved successfully!');
        }

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

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'cities');

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
