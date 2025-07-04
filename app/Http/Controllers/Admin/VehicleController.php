<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Station;
use App\Models\LadderMaker;
use App\Models\IbcCenter;



class VehicleController extends Controller
{
    public function index(){
        $vehicles = Vehicle::with(['vehicleType','station','ladderMaker','ibcCenter'])->where('is_active',1)->orderby('id','DESC')->get();
    	return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create(){
    	$serial_no = Vehicle::GetSerialNumber();
        $vehicleTypes = VehicleType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $stations = Station::where('is_active', 1)
            ->whereHas('ibcCenter', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['ibcCenter' => function ($query) {
                $query->where('is_active', 1)->orderBy('name', 'ASC');
            }])
            ->orderBy('area', 'ASC')
            ->get();

        $ladder_maker = LadderMaker::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $ibc_center = IbcCenter::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        return view('admin.vehicles.create',compact('serial_no','vehicleTypes','stations','status','ladder_maker','ibc_center'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_no'                =>  'required',
                'make'                      =>  'required',
                'model'                     =>  'required',
                'chasis_no'                 =>  'required',
                'engine_no'                 =>  'required',
                'ownership'                 =>  'required',
                'vehicle_type_id'           =>  'required',
                'cone'                      =>  'required|numeric|min:0',
                'station_id'                =>  'required',
                'ibc_center_id'             =>  'required',
                'ladder_maker_id'           =>  'required',
                'medical_box'               =>  'required',
                'seat_cover'                =>  'required',
                'fire_extenguisher'         =>  'required',
                'tracker_installation_date' =>  'required|date',
                'inspection_date'           =>  'required|date',
                'next_inspection_date'      =>  'required|date|after:inspection_date',
                'pso_card'                  =>  'required',
                'akpl'                      =>  'required',
                'registration_file'         =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'fitness_date'              =>  'required|date',
                'next_fitness_date'         =>  'required|date|after:fitness_date',
                'fitness_file'              =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'insurance_date'            =>  'required|date',
                'insurance_expiry_date'     =>  'required|date|after:insurance_date',
                'insurance_file'            =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'route_permit_date'         =>  'required|date',
                'route_permit_expiry_date'  =>  'required|date|after:route_permit_date',
                'route_permit_file'         =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',  
                'tax_date'                  =>  'required|date',
                'next_tax_date'             =>  'required|date|after:tax_date',
                'tax_file'                  =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',  
            ],
            [
                'vehicle_no.required'                   =>  'Vehicle No is required',
                'make.required'                         =>  'Make is required',
                'model.required'                        =>  'Model is required',
                'chasis_no.required'                    =>  'Chasis No is required',
                'engine_no.required'                    =>  'Engine No is required',
                'ownership.required'                    =>  'Ownership is required',
                'vehicle_type_id.required'              =>  'Vehicle type is required',
                'cone.required'                         =>  'Cone is required',
                'station_id.required'                   =>  'Station is required',
                'ibc_center_id.required'                =>  'IBC Center is required',
                'ladder_maker_id.required'              =>  'Ladder Maker is required',
                'medical_box.required'                  =>  'Medical Box is required',
                'seat_cover.required'                   =>  'Seat Cover is required',
                'fire_extenguisher.required'            =>  'Fire Extinguisher is required',
                'tracker_installation_date.required'    =>  'Tracker Installation Date is required',
                'inspection_date.required'              =>  'Inspection Date is required',
                'next_inspection_date.required'         =>  'Next Inspection Date is required',
                'pso_card.required'                     =>  'PSO Card Details is required',
                'akpl.required'                         =>  'AKPL is required',
                'registration_file.required'            =>  'Registration Attachment is required',
                'fitness_date.required'                 =>  'Fitness Date is required',
                'next_fitness_date.required'            =>  'Next fitness date is required',
                'fitness_file.required'                 =>  'Fitness Attachment is required',
                'insurance_date.required'               =>  'Insurance Date is required',
                'insurance_expiry_date.required'        =>  'Insurance Expiry Date is required',
                'insurance_file.required'               =>  'Insurance Attachment is required',
                'route_permit_date.required'            =>  'Route Permit Date is required',
                'route_permit_expiry_date.required'     =>  'Route Permit Expiry Date is required',
                'route_permit_file.required'            =>  'Route Permit Attachment is required',
                'tax_date.required'                     =>  'Tax Date is required',
                'next_tax_date.required'                =>  'Next Tax Date is required',
                'tax_file.required'                     =>  'Tax Attachment is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/vehicles');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $vehicle = new Vehicle();
        $vehicle->serial_no =   $request->serial_no;
        $vehicle->vehicle_no =   $request->vehicle_no;
        $vehicle->make =   $request->make;
        $vehicle->model =   $request->model;
        $vehicle->chasis_no =   $request->chasis_no;
        $vehicle->engine_no =   $request->engine_no;
        $vehicle->ownership =   $request->ownership;
        $vehicle->vehicle_type_id =   $request->vehicle_type_id;
        $vehicle->cone =   $request->cone;
        $vehicle->station_id =   $request->station_id;
        $vehicle->ibc_center_id =   $request->ibc_center_id;
        $vehicle->ladder_maker_id =   $request->ladder_maker_id;
        $vehicle->medical_box =   $request->medical_box;
        $vehicle->seat_cover =   $request->seat_cover;
        $vehicle->fire_extenguisher =   $request->fire_extenguisher;
        $vehicle->tracker_installation_date =   $request->tracker_installation_date;
        $vehicle->inspection_date =   $request->inspection_date;
        $vehicle->next_inspection_date =   $request->next_inspection_date;
        $vehicle->pso_card =   $request->pso_card;
        $vehicle->akpl =   $request->akpl;
        $vehicle->fitness_date =   $request->fitness_date;
        $vehicle->next_fitness_date =   $request->next_fitness_date;
        $vehicle->insurance_date =   $request->insurance_date;
        $vehicle->insurance_expiry_date =   $request->insurance_expiry_date;
        $vehicle->route_permit_date =   $request->route_permit_date;
        $vehicle->route_permit_expiry_date =   $request->route_permit_expiry_date;
        $vehicle->tax_date =   $request->tax_date;
        $vehicle->next_tax_date =   $request->next_tax_date;
        $vehicle->save();
        $vehicle_id = $vehicle->id;

        $registrationFileName = null;
        if ($request->hasFile('registration_file')) {
            $picture = $request->file('registration_file');
            $registrationFileName = time() . '_registration_file.' . $picture->getClientOriginalExtension();
            $picture->move($uploadPath, $registrationFileName);
        }

        $fitnessFileName = null;
        if ($request->hasFile('fitness_file')) {
            $authority = $request->file('fitness_file');
            $fitnessFileName = time() . '_fitness_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $fitnessFileName);
        }

        $insuranceFileName = null;
        if ($request->hasFile('insurance_file')) {
            $authority = $request->file('insurance_file');
            $insuranceFileName = time() . '_insurance_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $insuranceFileName);
        }

        $routePermitFileName = null;
        if ($request->hasFile('route_permit_file')) {
            $authority = $request->file('route_permit_file');
            $routePermitFileName = time() . '_route_permit_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $routePermitFileName);
        }

        $taxFileName = null;
        if ($request->hasFile('tax_file')) {
            $authority = $request->file('tax_file');
            $taxFileName = time() . '_tax_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $taxFileName);
        }

        $vehicle = Vehicle::find($vehicle_id);
        $vehicle->registration_file =   $registrationFileName;
        $vehicle->fitness_file      =   $fitnessFileName;
        $vehicle->insurance_file    =   $insuranceFileName;
        $vehicle->route_permit_file =   $routePermitFileName;
        $vehicle->tax_file          =   $taxFileName;
        $vehicle->save();

    	return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicleTypes = VehicleType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $stations = Station::where('is_active', 1)
            ->whereHas('ibcCenter', function ($query) {
                $query->where('is_active', 1);
            })
            ->with(['ibcCenter' => function ($query) {
                $query->where('is_active', 1)->orderBy('name', 'ASC');
            }])
            ->orderBy('area', 'ASC')
            ->get();

        $ladder_maker = LadderMaker::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $ibc_center = IbcCenter::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        return view('admin.vehicles.edit',compact('vehicle','vehicleTypes','stations','status','ladder_maker','ibc_center'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_no'                =>  'required',
                'make'                      =>  'required',
                'model'                     =>  'required',
                'chasis_no'                 =>  'required',
                'engine_no'                 =>  'required',
                'ownership'                 =>  'required',
                'vehicle_type_id'           =>  'required',
                'cone'                      =>  'required|numeric|min:0',
                'station_id'                =>  'required',
                'ibc_center_id'             =>  'required',
                'ladder_maker_id'           =>  'required',
                'medical_box'               =>  'required',
                'seat_cover'                =>  'required',
                'fire_extenguisher'         =>  'required',
                'tracker_installation_date' =>  'required|date',
                'inspection_date'           =>  'required|date',
                'next_inspection_date'      =>  'required|date|after:inspection_date',
                'pso_card'                  =>  'required',
                'akpl'                      =>  'required',
                'registration_file'         =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'fitness_date'              =>  'required|date',
                'next_fitness_date'         =>  'required|date|after:fitness_date',
                'fitness_file'              =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'insurance_date'            =>  'required|date',
                'insurance_expiry_date'     =>  'required|date|after:insurance_date',
                'insurance_file'            =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'route_permit_date'         =>  'required|date',
                'route_permit_expiry_date'  =>  'required|date|after:route_permit_date',
                'route_permit_file'         =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',  
                'tax_date'                  =>  'required|date',
                'next_tax_date'             =>  'required|date|after:tax_date',
                'tax_file'                  =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',  
            ],
            [
                'vehicle_no.required'                   =>  'Vehicle No is required',
                'make.required'                         =>  'Make is required',
                'model.required'                        =>  'Model is required',
                'chasis_no.required'                    =>  'Chasis No is required',
                'engine_no.required'                    =>  'Engine No is required',
                'ownership.required'                    =>  'Ownership is required',
                'vehicle_type_id.required'              =>  'Vehicle type is required',
                'cone.required'                         =>  'Cone is required',
                'station_id.required'                   =>  'Station is required',
                'ibc_center_id.required'                =>  'IBC Center is required',
                'ladder_maker_id.required'              =>  'Ladder Maker is required',
                'medical_box.required'                  =>  'Medical Box is required',
                'seat_cover.required'                   =>  'Seat Cover is required',
                'fire_extenguisher.required'            =>  'Fire Extinguisher is required',
                'tracker_installation_date.required'    =>  'Tracker Installation Date is required',
                'inspection_date.required'              =>  'Inspection Date is required',
                'next_inspection_date.required'         =>  'Next Inspection Date is required',
                'pso_card.required'                     =>  'PSO Card Details is required',
                'akpl.required'                         =>  'AKPL is required',
                'registration_file.required'            =>  'Registration Attachment is required',
                'fitness_date.required'                 =>  'Fitness Date is required',
                'next_fitness_date.required'            =>  'Next fitness date is required',
                'fitness_file.required'                 =>  'Fitness Attachment is required',
                'insurance_date.required'               =>  'Insurance Date is required',
                'insurance_expiry_date.required'        =>  'Insurance Expiry Date is required',
                'insurance_file.required'               =>  'Insurance Attachment is required',
                'route_permit_date.required'            =>  'Route Permit Date is required',
                'route_permit_expiry_date.required'     =>  'Route Permit Expiry Date is required',
                'route_permit_file.required'            =>  'Route Permit Attachment is required',
                'tax_date.required'                     =>  'Tax Date is required',
                'next_tax_date.required'                =>  'Next Tax Date is required',
                'tax_file.required'                     =>  'Tax Attachment is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/vehicles');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $vehicle->vehicle_no =   $request->vehicle_no;
        $vehicle->make =   $request->make;
        $vehicle->model =   $request->model;
        $vehicle->chasis_no =   $request->chasis_no;
        $vehicle->engine_no =   $request->engine_no;
        $vehicle->ownership =   $request->ownership;
        $vehicle->vehicle_type_id =   $request->vehicle_type_id;
        $vehicle->cone =   $request->cone;
        $vehicle->station_id =   $request->station_id;
        $vehicle->ibc_center_id =   $request->ibc_center_id;
        $vehicle->ladder_maker_id =   $request->ladder_maker_id;
        $vehicle->medical_box =   $request->medical_box;
        $vehicle->seat_cover =   $request->seat_cover;
        $vehicle->fire_extenguisher =   $request->fire_extenguisher;
        $vehicle->tracker_installation_date =   $request->tracker_installation_date;
        $vehicle->inspection_date =   $request->inspection_date;
        $vehicle->next_inspection_date =   $request->next_inspection_date;
        $vehicle->pso_card =   $request->pso_card;
        $vehicle->akpl =   $request->akpl;
        $vehicle->fitness_date =   $request->fitness_date;
        $vehicle->next_fitness_date =   $request->next_fitness_date;
        $vehicle->insurance_date =   $request->insurance_date;
        $vehicle->insurance_expiry_date =   $request->insurance_expiry_date;
        $vehicle->route_permit_date =   $request->route_permit_date;
        $vehicle->route_permit_expiry_date =   $request->route_permit_expiry_date;
        $vehicle->tax_date =   $request->tax_date;
        $vehicle->next_tax_date =   $request->next_tax_date;
        
        $registrationFileName = null;
        if ($request->hasFile('registration_file')) {
            $picture = $request->file('registration_file');
            $registrationFileName = time() . '_registration_file.' . $picture->getClientOriginalExtension();
            $picture->move($uploadPath, $registrationFileName);
            $vehicle->registration_file =   $registrationFileName;
        }

        $fitnessFileName = null;
        if ($request->hasFile('fitness_file')) {
            $authority = $request->file('fitness_file');
            $fitnessFileName = time() . '_fitness_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $fitnessFileName);
            $vehicle->fitness_file      =   $fitnessFileName;
        }

        $insuranceFileName = null;
        if ($request->hasFile('insurance_file')) {
            $authority = $request->file('insurance_file');
            $insuranceFileName = time() . '_insurance_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $insuranceFileName);
            $vehicle->insurance_file    =   $insuranceFileName;
        }

        $routePermitFileName = null;
        if ($request->hasFile('route_permit_file')) {
            $authority = $request->file('route_permit_file');
            $routePermitFileName = time() . '_route_permit_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $routePermitFileName);
            $vehicle->route_permit_file =   $routePermitFileName;
        }

        $taxFileName = null;
        if ($request->hasFile('tax_file')) {
            $authority = $request->file('tax_file');
            $taxFileName = time() . '_tax_file.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $taxFileName);
            $vehicle->tax_file          =   $taxFileName;
        }

        $vehicle->save();

        return redirect()->route('admin.vehicles.index')->with('success', 'Vehicle Updated successfully.');
    }

    public function show(Vehicle $vehicle)
    {
    	$register_on_portal = array(
            '1' =>  'Registered',
            '2' =>  'Not Registered',
        );
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        return view('admin.vehicles.show', compact('vehicle','register_on_portal','status'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->is_active = 0;
        $vehicle->save();
        
        return redirect()->route('admin.vehicles.index')->with('delete_msg', 'Vehicle deleted successfully.');
    }
}
