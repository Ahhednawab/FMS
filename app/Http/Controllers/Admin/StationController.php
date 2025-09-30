<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Models\Draft;
use App\Traits\DraftTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StationController extends Controller
{
    use DraftTrait;
    public function index()
    {
    	$stations = Station::where('is_active',1)->get();
    	return view('admin.stations.index',compact('stations'));
    }

    public function create(Request $request)
    {
        $serial_no = Station::GetSerialNumber();
        
        $draftData = $this->getDraftDataForView($request, 'stations');
        
        return view('admin.stations.create', compact('serial_no') + $draftData);
    }

    public function store(Request $request)
    {
        // Handle draft saving
        if ($this->handleDraftSave($request, 'stations')) {
            return redirect()->back()->with('success', 'Draft saved successfully!');
        }

        $validator = \Validator::make(
            $request->all(),
            [ 'area'         	=> 'required', ],
            [ 'area.required'        	=> 'Area is required', ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $station = new Station();
        $station->serial_no = $request->serial_no;
        $station->area = $request->area;
        $station->save();

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'stations');

        return redirect()->route('admin.stations.index')->with('success', 'Station created successfully.');
    }

    public function edit(Station $station)
    {
        return view('admin.stations.edit', compact('station'));
    }

    public function update(Request $request, Station $station)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'area'         	=> 'required',
            ],
            [
                'area.required'        	=> 'Area is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $station->area = $request->area;
        $station->save();

        return redirect()->route('admin.stations.index')->with('success', 'Station updated successfully.');
    }

    public function destroy(Station $station)
    {
        $station->is_active = 0;
        $station->save();

        return redirect()->route('admin.stations.index')->with('delete_msg', 'User deleted successfully.');
    }

    public function show(Station $station)
    {
    	return view('admin.stations.show', compact('station'));
    }
}
