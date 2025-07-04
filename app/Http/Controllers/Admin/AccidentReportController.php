<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccidentReport;
use Illuminate\Http\Request;

class AccidentReportController extends Controller
{
    public function index(){
        $accidentReports = AccidentReport::where('is_active',1)->get();
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
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        $primary_cause = array(
            '1' =>  'Human Error',
            '2' =>  'Mechanical Failure',
        );
        $investigation_status = array(
            '1' =>  'Status 1',
            '2' =>  'Status 2',
        );
        return view('admin.accidentReports.index', compact('accidentReports','accident_types','users','injury_types','damage_types','status','primary_cause','investigation_status'));
    }

    public function create(){
        $accident_report_id = AccidentReport::GetAccidentReportId();
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
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        $primary_cause = array(
            '1' =>  'Human Error',
            '2' =>  'Mechanical Failure',
        );
        $investigation_status = array(
            '1' =>  'Status 1',
            '2' =>  'Status 2',
        );
        return view('admin.accidentReports.create',compact('accident_report_id','accident_types','users','injury_types','damage_types','status','primary_cause','investigation_status'));
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
                'witness_involved' => 'required',
                'vehicle_no' => 'required',
                'primary_cause' => 'required',
                'medical_provided' => 'required',
                'police_report_filed' => 'required',
                'investigation_status' => 'required',
                'insurance_claimed' => 'required',
                'insurance_doc' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'police_report_file' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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
                'witness_involved.required'     =>  'Witness Involved is required',
                'vehicle_no.required'           =>  'Vehicle No is required',
                'primary_cause.required'        =>  'Primary Cause is required',
                'medical_provided.required'     =>  'Medical Provided is required',
                'police_report_filed.required'  =>  'Police Report Filed is required',
                'investigation_status.required' =>  'Investigation Status is required',
                'insurance_claimed.required'    =>  'Insurance Claimed is required',
                'insurance_doc.required'        =>  'Insurance Docs is required',
                'insurance_doc.mimes'           =>  'Insurance Docs must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
                'police_report_file.required'   =>  'Police Report File is required',
                'police_report_file.mimes'      =>  'Insurance Docs must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/accidentReports');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $accidentReport = new AccidentReport();
        $accidentReport->accident_report_id     =   $request->accident_report_id;
        $accidentReport->accident_type          =   $request->accident_type;
        $accidentReport->location               =   $request->location;
        $accidentReport->accident_date          =   $request->accident_date;
        $accidentReport->accident_time          =   $request->accident_time;
        $accidentReport->accident_description   =   $request->accident_description;
        $accidentReport->person_involved        =   $request->person_involved;
        $accidentReport->injury_type            =   $request->injury_type;
        $accidentReport->damage_type            =   $request->damage_type;
        $accidentReport->witness_involved       =   $request->witness_involved;
        $accidentReport->vehicle_no             =   $request->vehicle_no;
        $accidentReport->primary_cause          =   $request->primary_cause;
        $accidentReport->medical_provided       =   $request->medical_provided;
        $accidentReport->police_report_filed    =   $request->police_report_filed;
        $accidentReport->investigation_status   =   $request->investigation_status;
        $accidentReport->insurance_claimed      =   $request->insurance_claimed;
        $accidentReport->save();
        $id = $accidentReport->id;

        $insuranceDocFileName = null;
        if ($request->hasFile('insurance_doc')) {
            $picture = $request->file('insurance_doc');
            $insuranceDocFileName = time() . '_insurance_doc.' . $picture->getClientOriginalExtension();
            $picture->move($uploadPath, $insuranceDocFileName);
        }

        $policeReportFileFileName = null;
        if ($request->hasFile('police_report_file')) {
            $authority = $request->file('police_report_file');
            $policeReportFileFileName = time() . '_police_report.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $policeReportFileFileName);
        }

        $accidentReport = AccidentReport::find($id);
        $accidentReport->insurance_doc = $insuranceDocFileName;
        $accidentReport->police_report_file = $policeReportFileFileName;
        $accidentReport->save();

        return redirect()->route('admin.accidentReports.index')->with('success', 'Accident Report created successfully.');
    }

    public function update(Request $request, AccidentReport $accidentReport)
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
                'witness_involved' => 'required',
                'vehicle_no' => 'required',
                'primary_cause' => 'required',
                'medical_provided' => 'required',
                'police_report_filed' => 'required',
                'investigation_status' => 'required',
                'insurance_claimed' => 'required',
                'insurance_doc' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'police_report_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
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
                'witness_involved.required'     =>  'Witness Involved is required',
                'vehicle_no.required'           =>  'Vehicle No is required',
                'primary_cause.required'        =>  'Primary Cause is required',
                'medical_provided.required'     =>  'Medical Provided is required',
                'police_report_filed.required'  =>  'Police Report Filed is required',
                'investigation_status.required' =>  'Investigation Status is required',
                'insurance_claimed.required'    =>  'Insurance Claimed is required',
                'insurance_doc.mimes'           =>  'Insurance Docs must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
                'police_report_file.mimes'      =>  'Insurance Docs must be a file of type: pdf, doc, docx, jpg, jpeg, png.',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/accidentReports');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $accidentReport->accident_type          =   $request->accident_type;
        $accidentReport->location               =   $request->location;
        $accidentReport->accident_date          =   $request->accident_date;
        $accidentReport->accident_time          =   $request->accident_time;
        $accidentReport->accident_description   =   $request->accident_description;
        $accidentReport->person_involved        =   $request->person_involved;
        $accidentReport->injury_type            =   $request->injury_type;
        $accidentReport->damage_type            =   $request->damage_type;
        $accidentReport->witness_involved       =   $request->witness_involved;
        $accidentReport->vehicle_no             =   $request->vehicle_no;
        $accidentReport->primary_cause          =   $request->primary_cause;
        $accidentReport->medical_provided       =   $request->medical_provided;
        $accidentReport->police_report_filed    =   $request->police_report_filed;
        $accidentReport->investigation_status   =   $request->investigation_status;
        $accidentReport->insurance_claimed      =   $request->insurance_claimed;
        $accidentReport->save();
        $id = $accidentReport->id;

        $insuranceDocFileName = null;
        if ($request->hasFile('insurance_doc')) {
            $picture = $request->file('insurance_doc');
            $insuranceDocFileName = time() . '_insurance_doc.' . $picture->getClientOriginalExtension();
            $picture->move($uploadPath, $insuranceDocFileName);
        }

        $policeReportFileFileName = null;
        if ($request->hasFile('police_report_file')) {
            $authority = $request->file('police_report_file');
            $policeReportFileFileName = time() . '_police_report.' . $authority->getClientOriginalExtension();
            $authority->move($uploadPath, $policeReportFileFileName);
        }

        $accidentReport = AccidentReport::find($id);
        $accidentReport->insurance_doc = $insuranceDocFileName;
        $accidentReport->police_report_file = $policeReportFileFileName;
        $accidentReport->save();

        return redirect()->route('admin.accidentReports.index')->with('success', 'Accident Report updated successfully.');
    }

    public function show(AccidentReport $accidentReport)
    {
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
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        $primary_cause = array(
            '1' =>  'Human Error',
            '2' =>  'Mechanical Failure',
        );
        $investigation_status = array(
            '1' =>  'Status 1',
            '2' =>  'Status 2',
        );
        
        return view('admin.accidentReports.show', compact('accidentReport','accident_types','users','injury_types','damage_types','status','primary_cause','investigation_status'));
    }

    public function edit(AccidentReport $accidentReport)
    {
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
        $status = array(
            '1' =>  'Yes',
            '2' =>  'No',
        );
        $primary_cause = array(
            '1' =>  'Human Error',
            '2' =>  'Mechanical Failure',
        );
        $investigation_status = array(
            '1' =>  'Status 1',
            '2' =>  'Status 2',
        );
        return view('admin.accidentReports.edit',compact('accidentReport','accident_types','users','injury_types','damage_types','status','primary_cause','investigation_status'));
    }

    public function destroy(AccidentReport $accidentReport)
    {
        $accidentReport->is_active = 0;
        $accidentReport->save();
        return redirect()->route('admin.accidentReports.index')->with('delete_msg', 'Accident Report deleted successfully.');
    }
}
