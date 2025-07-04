<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccidentDetail;
use Illuminate\Http\Request;

class AccidentDetailController extends Controller
{
    public function index(){
        $accidentDetails = AccidentDetail::where('is_active',1)->get();
        $accident_types = array(
            '1' =>  'Accident 1',
            '2' =>  'Accident 2',
            '3' =>  'Accident 3',
        );
        $users = array(
            '1' =>  'User 1',
            '2' =>  'User 2',
            '3' =>  'User 3',
        );
        $injury_types = array(
            '1' =>  'Minor',
            '2' =>  'Moderate',
            '3' =>  'Severe',
        );
        $damage_types = array(
            '1' =>  'Vehicle',
            '2' =>  'Equipment',
        );
        return view('admin.accidentDetails.index', compact('accidentDetails','accident_types','users','injury_types','damage_types'));
    }

    public function create(){
        $accident_id = AccidentDetail::GetAccidentId();
        $accident_types = array(
            '1'     =>  'Accident 1',
            '2' =>  'Accident 2',
            '3' =>  'Accident 3',
        );
        $users = array(
            '1' =>  'User 1',
            '2' =>  'User 2',
            '3' =>  'User 3',
        );
        $injury_types = array(
            '1' =>  'Minor',
            '2' =>  'Moderate',
            '3' =>  'Severe',
        );
        $damage_types = array(
            '1' =>  'Vehicle',
            '2' =>  'Equipment',
        );
        return view('admin.accidentDetails.create',compact('accident_id','accident_types','users','injury_types','damage_types'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'accident_type' => 'required',
                'location' => 'required',
                'accident_date' => 'required',
                'accident_time' => 'required',
                'accident_description' => 'required',
                'person_involved' => 'required',
                'injury_type' => 'required',
                'damage_type' => 'required',
            ],
            [
                'accident_type.required'        =>  'Accident Type is required',
                'location.required'             =>  'Location is required',
                'accident_date.required'        =>  'Accident Date is required',
                'accident_time.required'        =>  'Accident Time is required',
                'accident_description.required' =>  'Accident Description is required',
                'person_involved.required'      =>  'Person Involved is required',
                'injury_type.required'          =>  'Injury Type is required',
                'damage_type.required'          =>  'Damage Type is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $accidentDetail = new AccidentDetail();
        $accidentDetail->accident_id            =   $request->accident_id;
        $accidentDetail->accident_type          =   $request->accident_type;
        $accidentDetail->location               =   $request->location;
        $accidentDetail->accident_date          =   $request->accident_date;
        $accidentDetail->accident_time          =   $request->accident_time;
        $accidentDetail->accident_description   =   $request->accident_description;
        $accidentDetail->person_involved        =   $request->person_involved;
        $accidentDetail->injury_type            =   $request->injury_type;
        $accidentDetail->damage_type            =   $request->damage_type;
        $accidentDetail->save();

        return redirect()->route('admin.accidentDetails.index')->with('success', 'Accident Details created successfully.');
    }

    public function edit(AccidentDetail $accidentDetail)
    {
        $accident_types = array(
            '1'     =>  'Accident 1',
            '2' =>  'Accident 2',
            '3' =>  'Accident 3',
        );
        $users = array(
            '1' =>  'User 1',
            '2' =>  'User 2',
            '3' =>  'User 3',
        );
        $injury_types = array(
            '1' =>  'Minor',
            '2' =>  'Moderate',
            '3' =>  'Severe',
        );
        $damage_types = array(
            '1' =>  'Vehicle',
            '2' =>  'Equipment',
        );
        return view('admin.accidentDetails.edit', compact('accidentDetail','accident_types','users','injury_types','damage_types'));
    }

    public function update(Request $request, AccidentDetail $accidentDetail)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'accident_type' => 'required',
                'location' => 'required',
                'accident_date' => 'required',
                'accident_time' => 'required',
                'accident_description' => 'required',
                'person_involved' => 'required',
                'injury_type' => 'required',
                'damage_type' => 'required',
            ],
            [
                'accident_type.required'        =>  'Accident Type is required',
                'location.required'             =>  'Location is required',
                'accident_date.required'        =>  'Accident Date is required',
                'accident_time.required'        =>  'Accident Time is required',
                'accident_description.required' =>  'Accident Description is required',
                'person_involved.required'      =>  'Person Involved is required',
                'injury_type.required'          =>  'Injury Type is required',
                'damage_type.required'          =>  'Damage Type is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $accidentDetail->accident_type          =   $request->accident_type;
        $accidentDetail->location               =   $request->location;
        $accidentDetail->accident_date          =   $request->accident_date;
        $accidentDetail->accident_time          =   $request->accident_time;
        $accidentDetail->accident_description   =   $request->accident_description;
        $accidentDetail->person_involved        =   $request->person_involved;
        $accidentDetail->injury_type            =   $request->injury_type;
        $accidentDetail->damage_type            =   $request->damage_type;
        $accidentDetail->save();

        return redirect()->route('admin.accidentDetails.index')->with('success', 'Accident Details updated successfully.');
    }

    public function show(AccidentDetail $accidentDetail)
    {
        $accident_types = array(
            '1'     =>  'Accident 1',
            '2' =>  'Accident 2',
            '3' =>  'Accident 3',
        );
        $users = array(
            '1' =>  'User 1',
            '2' =>  'User 2',
            '3' =>  'User 3',
        );
        $injury_types = array(
            '1' =>  'Minor',
            '2' =>  'Moderate',
            '3' =>  'Severe',
        );
        $damage_types = array(
            '1' =>  'Vehicle',
            '2' =>  'Equipment',
        );
        return view('admin.accidentDetails.show', compact('accidentDetail','accident_types','users','injury_types','damage_types'));
    }

    public function destroy(AccidentDetail $accidentDetail)
    {
        $accidentDetail->is_active = 0;
        $accidentDetail->save();
        return redirect()->route('admin.accidentDetails.index')->with('delete_msg', 'Accident Details deleted successfully.');
    }
}
