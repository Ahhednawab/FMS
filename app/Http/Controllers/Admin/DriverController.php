<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Designation;
use App\Models\DriverStatus;
use App\Models\Vehicle;
use App\Models\MaritalStatus;
use App\Models\LicenseCategory;
use App\Models\ShiftTimings;
use App\Traits\DraftTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DriverController extends Controller
{
    use DraftTrait;
    public function index()
    {
        $drivers = Driver::with(['driverStatus','vehicle','maritalStatus','licenseCategory','shiftTiming'])
            ->where('is_active',1)->latest()
            ->get();

        // echo "<pre>";
        // print_r($drivers[0]);
        // echo "</pre>";
        // return;

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create(Request $request)
    {
        $serial_no = Driver::GetSerialNumber();
        $driver_status = DriverStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        $vehicles = Vehicle::where('is_active', 1)
            ->whereNotIn('id', function ($query) {
                // Exclude vehicles where shift_hour_id = 1 and already has a driver
                $query->select('v.id')
                    ->from('vehicles as v')
                    ->join('drivers as d', 'v.id', '=', 'd.vehicle_id')
                    ->where('v.shift_hour_id', 1)
                    ->where('d.is_active', 1);
            })
            ->whereNotIn('id', function ($query) {
                // Exclude vehicles where shift_hour_id = 2 and has 2 drivers with different shift_timing_id
                $query->select('v.id')
                    ->from('vehicles as v')
                    ->join('drivers as d', 'v.id', '=', 'd.vehicle_id')
                    ->where('v.shift_hour_id', 2)
                    ->where('d.is_active', 1)
                    ->groupBy('v.id')
                    ->havingRaw('COUNT(DISTINCT d.shift_timing_id) >= 2');
            })
            ->orderBy('vehicle_no')
            ->pluck('vehicle_no', 'id');

        $marital_status = MaritalStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $licence_category = LicenseCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $shift_timings = ShiftTimings::select('id','name','start_time','end_time')
            ->where('is_active', 1)
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(function($shift) {
                return [
                    $shift->id => $shift->name . ' (' 
                        . date('h:i a', strtotime($shift->start_time)) 
                        . ' - ' 
                        . date('h:i a', strtotime($shift->end_time)) 
                        . ')'
                ];
            })
            ->toArray();

        $status = array(
            'yes'   =>  'Yes',
            'no'    =>  'No',
        );
        $draftInfo = $this->getDraftDataForView($request, 'drivers');
        
        return view('admin.drivers.create', compact('serial_no','driver_status','marital_status','licence_category','status','vehicles','shift_timings') + $draftInfo);
    }

    public function store(Request $request)
    {
        // Handle draft saving
        if ($this->handleDraftSave($request, 'drivers')) {
            return redirect()->back()->with('success', 'Draft saved successfully!');
        }
        // If coming from a draft, relax file requirements when already attached in draft
        $draftAttached = [
            'cnic_file' => false,
            'eobi_card_file' => false,
            'picture_file' => false,
            'medical_report_file' => false,
            'authority_letter_file' => false,
            'employee_card_file' => false,
            'ddc_file' => false,
            'third_party_driver_file' => false,
            'license_file' => false,
        ];
        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = \App\Models\Draft::where('id', $request->draft_id)
                ->where('created_by', auth()->id())
                ->where('module', 'drivers')
                ->first();
            if ($draft && is_array($draft->file_info)) {
                foreach ($draftAttached as $field => $v) {
                    $draftAttached[$field] = isset($draft->file_info[$field]);
                }
            }
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'full_name' =>  'nullable',
                'father_name' =>  'nullable',
                'mother_name' =>  'nullable',
                'phone' =>  'nullable|string|size:12',
                'salary' =>  'nullable|numeric',
                'account_no' =>  'nullable',
                'driver_status_id' =>  'nullable',
                'marital_status_id' =>  'nullable',
                'dob' =>  'nullable|date',
                'vehicle_id' =>  'nullable|unique:drivers,vehicle_id',
                'shift_timing_id' =>  'nullable|exists:shift_timing,id',
                'cnic_no' =>  'required|unique:drivers,cnic_no|string|size:15',
                'cnic_expiry_date' =>  'required|date',
                'cnic_file' =>  ($draftAttached['cnic_file'] ? 'nullable' : 'required') . '|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'eobi_no' =>  'required',
                // 'eobi_start_date' =>  'required',
                // 'eobi_card_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'picture_file' =>  ($draftAttached['picture_file'] ? 'nullable' : 'required') . '|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'medical_report_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'authority_letter_file' =>  ($draftAttached['authority_letter_file'] ? 'nullable' : 'nullable') . '|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'employment_date' =>  'nullable|date',
                'employee_card_file' =>  ($draftAttached['employee_card_file'] ? 'nullable' : 'nullable') . '|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'ddc_file' =>  ($draftAttached['ddc_file'] ? 'nullable' : 'nullable') . '|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'third_party_driver_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'license_no' =>  'required|unique:drivers,license_no',
                'license_category_id' =>  'required',
                'license_expiry_date' =>  'required|date',
                'license_file' =>  ($draftAttached['license_file'] ? 'nullable' : 'required') . '|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'uniform_issue_date'    =>  'nullable|date',
                'sandal_issue_date'    =>  'nullable|date',
                'address' =>  'nullable',
            ],
            [
                'full_name.required'                =>  'Full Name is required',
                'father_name.required'              =>  'Father Name is required',
                'mother_name.required'              =>  'Mother Name is required',
                'phone.required'                    =>  'Cell Phone No is required',
                'salary.required'                   =>  'Salary is required',
                'account_no.required'               =>  'Account No is required',
                'driver_status_id.required'         =>  'Status is required',
                'marital_status_id.required'        =>  'Marital Status is required',
                'dob.required'                      =>  'DOB is required',
                // 'vehicle_id.required'               =>  'Vehicle Number is required',
                'vehicle_id.required_if'            => 'Vehicle Number is required.',
                'shift_timing_id.required'          => 'Shift Timing is required.',
                'shift_timing_id.exists'            => 'Selected Shift Timing is invalid.',
                'cnic_no.required'                  =>  'CNIC No is required',
                'cnic_expiry_date.required'         =>  'CNIC Expiry Date is required',
                'cnic_file.required'                =>  'CNIC is required',
                'cnic_file.mimes'                   =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                // 'eobi_no.required'                  =>  'EOBI No is required',
                // 'eobi_start_date.required'          =>  'EOBI Start Date is required',
                // 'eobi_card_file.required'           =>  'EOBI Card is required',
                // 'eobi_card_file.mimes'              =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'picture_file.required'             =>  'Picture is required',
                // 'medical_report_file.required'      =>  'Medical Report is required',
                'authority_letter_file.required'    =>  'Authority Letter is required',
                'authority_letter_file.mimes'       =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'employment_date.required'          =>  'Employment Date is required',
                'employee_card_file.required'       =>  'Employee Card is required',
                'employee_card_file.mimes'          =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'ddc_file.required'                 =>  'DDC is required',
                'ddc_file.mimes'                    =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'third_party_driver_file.mimes'     =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'license_no.required'               =>  'License No is required',
                'license_category_id.required'      =>  'License Category is required',
                'license_expiry_date.required'      =>  'License Expiry Date is required',
                'license_file.required'             =>  'License is required',
                'license_file.mimes'                =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'uniform_issue_date.required'       =>  'Uniform Date is required',
                'sandal_issue_date.required'        =>  'Sandal Date is required',
                'address.required'                  =>  'Address is required',
            ]
        );
        
        // Add custom validation for shift timing conflict
        $validator->after(function ($validator) use ($request) {
            if ($request->vehicle_id && $request->shift_timing_id) {
                $vehicle = Vehicle::find($request->vehicle_id);
                if ($vehicle && $vehicle->shift_hour_id == 2) {
                    $existingDriver = Driver::where('vehicle_id', $request->vehicle_id)
                        ->where('shift_timing_id', $request->shift_timing_id)
                        ->where('is_active', 1)
                        ->first();
                    
                    if ($existingDriver) {
                        $validator->errors()->add('shift_timing_id', 'This vehicle already has a driver assigned to this shift timing. Please select a different shift timing.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $uploadPath = public_path('uploads/drivers');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $driver = new Driver();
        $driver->serial_no              =   $request->serial_no;
        $driver->full_name              =   $request->full_name;
        $driver->father_name            =   $request->father_name;
        $driver->mother_name            =   $request->mother_name;
        $driver->phone                  =   $request->phone;
        $driver->salary                 =   $request->salary;
        $driver->account_no             =   $request->account_no;
        $driver->driver_status_id       =   $request->driver_status_id;
        $driver->marital_status_id      =   $request->marital_status_id;
        $driver->dob                    =   $request->dob;
        $driver->vehicle_id             =   $request->vehicle_id;
        $driver->shift_timing_id        =   $request->shift_timing_id;
        $driver->cnic_no                =   $request->cnic_no;
        $driver->cnic_expiry_date       =   $request->cnic_expiry_date;
        $driver->eobi_no                =   $request->eobi_no;
        $driver->eobi_start_date        =   $request->eobi_start_date;
        $driver->employment_date        =   $request->employment_date;
        $driver->license_no             =   $request->license_no;
        $driver->license_category_id    =   $request->license_category_id;
        $driver->license_expiry_date    =   $request->license_expiry_date;
        $driver->uniform_issue_date    =   $request->uniform_issue_date;
        $driver->sandal_issue_date    =   $request->sandal_issue_date;
        $driver->address                =   $request->address;
        
        $cnicFileName = null;
        if ($request->hasFile('cnic_file')) {
            $cnic = $request->file('cnic_file');
            $cnicFileName = time() . '_cnic_file.' . $cnic->getClientOriginalExtension();
            $cnic->move($uploadPath, $cnicFileName);

            $driver->cnic_file  =   $cnicFileName;
        }

        $eobiCardFileName = null;
        if ($request->hasFile('eobi_card_file')) {
            $eobi = $request->file('eobi_card_file');
            $eobiCardFileName = time() . '_eobi_card_file.' . $eobi->getClientOriginalExtension();
            $eobi->move($uploadPath, $eobiCardFileName);

            $driver->eobi_card_file =   $eobiCardFileName;
        }

        $pictureFileName = null;
        if ($request->hasFile('picture_file')) {
            $picture = $request->file('picture_file');
            $pictureFileName = time() . '_picture_file.' . $picture->getClientOriginalExtension();
            $picture->move($uploadPath, $pictureFileName);

            $driver->picture_file   =   $pictureFileName;
        }

        $medicalReportFileName = null;
        if ($request->hasFile('medical_report_file')) {
            $medicalReport = $request->file('medical_report_file');
            $medicalReportFileName = time() . '_medical_report_file.' . $medicalReport->getClientOriginalExtension();
            $medicalReport->move($uploadPath, $medicalReportFileName);

            $driver->medical_report_file    =   $medicalReportFileName;
        }

        $authorityLetterFileName = null;
        if ($request->hasFile('authority_letter_file')) {
            $authorityLetter = $request->file('authority_letter_file');
            $authorityLetterFileName = time() . '_authority_letter_file.' . $authorityLetter->getClientOriginalExtension();
            $authorityLetter->move($uploadPath, $authorityLetterFileName);

            $driver->authority_letter_file  =   $authorityLetterFileName;
        }

        $employeeCardFileName = null;
        if ($request->hasFile('employee_card_file')) {
            $employeeCard = $request->file('employee_card_file');
            $employeeCardFileName = time() . '_employee_card_file.' . $employeeCard->getClientOriginalExtension();
            $employeeCard->move($uploadPath, $employeeCardFileName);

            $driver->employee_card_file =   $employeeCardFileName;
        }

        $ddcFileName = null;
        if ($request->hasFile('ddc_file')) {
            $ddc = $request->file('ddc_file');
            $ddcFileName = time() . '_ddc_file.' . $ddc->getClientOriginalExtension();
            $ddc->move($uploadPath, $ddcFileName);

            $driver->ddc_file   =   $ddcFileName;
        }

        $thirdPartyFileName = null;
        if ($request->hasFile('third_party_driver_file')) {
            $thirdParty = $request->file('third_party_driver_file');
            $thirdPartyFileName = time() . '_third_party_driver_file.' . $thirdParty->getClientOriginalExtension();
            $thirdParty->move($uploadPath, $thirdPartyFileName);

            $driver->third_party_driver_file    =   $thirdPartyFileName;
        }

        $licenseFileName = null;
        if ($request->hasFile('license_file')) {
            $license = $request->file('license_file');
            $licenseFileName = time() . '_license_file.' . $license->getClientOriginalExtension();
            $license->move($uploadPath, $licenseFileName);

            $driver->license_file   =   $licenseFileName;
        }

        $driver->save();

        // If this submission came from a draft and some files were not re-uploaded,
        // move existing draft files to permanent uploads and attach to the driver
        if ($request->filled('draft_id')) {
            $draft = \App\Models\Draft::where('id', $request->draft_id)
                ->where('created_by', auth()->id())
                ->where('module', 'drivers')
                ->first();
            if ($draft && is_array($draft->file_info)) {
                $map = [
                    'cnic_file' => 'cnic_file',
                    'eobi_card_file' => 'eobi_card_file',
                    'picture_file' => 'picture_file',
                    'medical_report_file' => 'medical_report_file',
                    'authority_letter_file' => 'authority_letter_file',
                    'employee_card_file' => 'employee_card_file',
                    'ddc_file' => 'ddc_file',
                    'third_party_driver_file' => 'third_party_driver_file',
                    'license_file' => 'license_file',
                ];
                $permanentDir = public_path('uploads/drivers');
                if (!file_exists($permanentDir)) {
                    @mkdir($permanentDir, 0755, true);
                }
                $updated = false;
                foreach ($map as $field => $attr) {
                    // Skip if user uploaded a new file already
                    if (!empty($driver->{$attr})) { continue; }
                    $info = $draft->file_info[$field] ?? null;
                    if (!$info || empty($info['path'])) { continue; }
                    $draftFull = public_path($info['path']);
                    if (!file_exists($draftFull)) { continue; }
                    $ext = pathinfo($draftFull, PATHINFO_EXTENSION);
                    $filename = time() . '_' . $field . '.' . $ext;
                    $dest = $permanentDir . DIRECTORY_SEPARATOR . $filename;
                    @rename($draftFull, $dest);
                    if (!file_exists($dest)) {
                        @File::copy($draftFull, $dest);
                        @unlink($draftFull);
                    }
                    if (file_exists($dest)) {
                        $driver->{$attr} = $filename;
                        $updated = true;
                    }
                }
                if ($updated) {
                    $driver->save();
                }
            }
        }

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'drivers');

        return redirect()->route('admin.drivers.index')->with('success', 'Driver created successfully.');
    }

    public function edit(Driver $driver){
        $driver_status = DriverStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        $vehicles = Vehicle::where('is_active', 1)
            ->whereNotIn('id', function ($query) use ($driver) {
                // Exclude vehicles where shift_hour_id = 1 and already has a driver (except current driver)
                $query->select('v.id')
                    ->from('vehicles as v')
                    ->join('drivers as d', 'v.id', '=', 'd.vehicle_id')
                    ->where('v.shift_hour_id', 1)
                    ->where('d.is_active', 1)
                    ->where('d.id', '!=', $driver->id);
            })
            ->whereNotIn('id', function ($query) use ($driver) {
                // Exclude vehicles where shift_hour_id = 2 and has 2 drivers with different shift_timing_id (except current driver)
                $query->select('v.id')
                    ->from('vehicles as v')
                    ->join('drivers as d', 'v.id', '=', 'd.vehicle_id')
                    ->where('v.shift_hour_id', 2)
                    ->where('d.is_active', 1)
                    ->where('d.id', '!=', $driver->id)
                    ->groupBy('v.id')
                    ->havingRaw('COUNT(DISTINCT d.shift_timing_id) >= 2');
            })
            ->orderBy('vehicle_no')
            ->pluck('vehicle_no', 'id');

        $marital_status = MaritalStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $licence_category = LicenseCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $shift_timings = ShiftTimings::select('id','name','start_time','end_time')
            ->where('is_active', 1)
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(function($shift) {
                return [
                    $shift->id => $shift->name . ' (' 
                        . date('h:i a', strtotime($shift->start_time)) 
                        . ' - ' 
                        . date('h:i a', strtotime($shift->end_time)) 
                        . ')'
                ];
            })
            ->toArray();
        
        $status = array(
            'yes'   =>  'Yes',
            'no'    =>  'No',
        );
        return view('admin.drivers.edit', compact('driver','driver_status','marital_status','licence_category','status','vehicles','shift_timings'));
    }

    public function update(Request $request, Driver $driver)
    {
        $rules = [
                'full_name' =>  'nullable',
                'father_name' =>  'nullable',
                'mother_name' =>  'nullable',
                'phone' =>  'nullable|string|size:12',
                'salary' =>  'nullable|numeric',
                'account_no' =>  'nullable',
                'driver_status_id' =>  'nullable',
                'marital_status_id' =>  'nullable',
                'dob' =>  'nullable|date',
                // 'vehicle_id' =>  'required',
                
                'shift_timing_id' =>  'nullable|exists:shift_timing,id',
                // 'cnic_no' =>  'required|string|size:15|unique:drivers,cnic_no,' . $driver->id,
                'cnic_expiry_date' =>  'required|date',
                'cnic_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'eobi_no' =>  'required',
                // 'eobi_start_date' =>  'required',
                // 'eobi_card_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'picture_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'medical_report_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'authority_letter_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'employment_date' =>  'nullable|date',
                'employee_card_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'ddc_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'third_party_driver_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'license_no' =>  'required |unique:drivers,license_no,' . $driver->id,
                'license_category_id' =>  'required',
                'license_expiry_date' =>  'required|date',
                'license_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'uniform_issue_date'    =>  'nullable|date',
                'sandal_issue_date'    =>  'nullable|date',
                'address' =>  'nullable',
            ];
            if ($request->has('vehicle_id') && $request->vehicle_id != $driver->vehicle_id) {
                $rules['vehicle_id'] = 'nullable|unique:drivers,vehicle_id';
            } else {
                $rules['vehicle_id'] = 'nullable';
            }
            if ($request->has('cnic_no') && $request->cnic_no != $driver->cnic_no) {
                $rules['cnic_no'] = 'required|string|size:15|unique:drivers,cnic_no';
            } else {
                $rules['cnic_no'] = 'required|string|size:15';
            }
            if ($request->has('license_no') && $request->license_no != $driver->license_no) {
                $rules['license_no'] = 'required|unique:drivers,license_no';
            } else {
                $rules['license_no'] = 'required';
            }



        $validator = \Validator::make(
            $request->all(),
            $rules,
            [
                'full_name.required'                =>  'Full Name is required',
                'father_name.required'              =>  'Father Name is required',
                'mother_name.required'              =>  'Mother Name is required',
                'phone.required'                    =>  'Cell Phone No is required',
                'salary.required'                   =>  'Salary is required',
                'account_no.required'               =>  'Account No is required',
                'driver_status_id.required'         =>  'Status is required',
                'marital_status_id.required'        =>  'Marital Status is required',
                'dob.required'                      =>  'DOB is required',
                'vehicle_id.required_if'            => 'Vehicle Number is required.',
                'shift_timing_id.required'          => 'Shift Timing is required.',
                'shift_timing_id.exists'            => 'Selected Shift Timing is invalid.',
                'cnic_no.required'                  =>  'CNIC No is required',
                'cnic_expiry_date.required'         =>  'CNIC Expiry Date is required',
                'cnic_file.required'                =>  'CNIC is required',
                'cnic_file.mimes'                   =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                // 'eobi_no.required'                  =>  'EOBI No is required',
                // 'eobi_start_date.required'          =>  'EOBI Start Date is required',
                // 'eobi_card_file.required'           =>  'EOBI Card is required',
                // 'eobi_card_file.mimes'              =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'picture_file.required'             =>  'Picture is required',
                // 'medical_report_file.required'      =>  'Medical Report is required',
                'authority_letter_file.required'    =>  'Authority Letter is required',
                'authority_letter_file.mimes'       =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'employment_date.required'          =>  'Employment Date is required',
                'employee_card_file.required'       =>  'Employee Card is required',
                'employee_card_file.mimes'          =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'ddc_file.required'                 =>  'DDC is required',
                'ddc_file.mimes'                    =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'third_party_driver_file.mimes'     =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'license_no.required'               =>  'License No is required',
                'license_category_id.required'      =>  'License Category is required',
                'license_expiry_date.required'      =>  'License Expiry Date is required',
                'license_file.required'             =>  'License is required',
                'license_file.mimes'                =>  'File must be type: pdf, doc, docx, jpg, jpeg, png.',
                'uniform_issue_date.required'       =>  'Uniform Date is required',
                'sandal_issue_date.required'        =>  'Sandlal Date is required',
                'address.required'                  =>  'Address is required',
            ]
        );
        
        // Add custom validation for shift timing conflict (excluding current driver)
        $validator->after(function ($validator) use ($request, $driver) {
            if ($request->vehicle_id && $request->shift_timing_id) {
                $vehicle = Vehicle::find($request->vehicle_id);
                if ($vehicle && $vehicle->shift_hour_id == 2) {
                    $existingDriver = Driver::where('vehicle_id', $request->vehicle_id)
                        ->where('shift_timing_id', $request->shift_timing_id)
                        ->where('is_active', 1)
                        ->where('id', '!=', $driver->id)
                        ->first();
                    
                    if ($existingDriver) {
                        $validator->errors()->add('shift_timing_id', 'This vehicle already has a driver assigned to this shift timing. Please select a different shift timing.');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/drivers');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $driver->full_name              =   $request->full_name;
        $driver->father_name            =   $request->father_name;
        $driver->mother_name            =   $request->mother_name;
        $driver->phone                  =   $request->phone;
        $driver->salary                 =   $request->salary;
        $driver->account_no             =   $request->account_no;
        $driver->driver_status_id       =   $request->driver_status_id;
        $driver->marital_status_id      =   $request->marital_status_id;
        $driver->dob                    =   $request->dob;
        $driver->vehicle_id             =   ($request->driver_status_id == 1) ? $request->vehicle_id : NULL;
        $driver->shift_timing_id        =   ($request->driver_status_id == 1) ? $request->shift_timing_id : NULL;
        $driver->cnic_no                =   $request->cnic_no;
        $driver->cnic_expiry_date       =   $request->cnic_expiry_date;
        $driver->eobi_no                =   $request->eobi_no;
        $driver->eobi_start_date        =   $request->eobi_start_date;
        $driver->employment_date        =   $request->employment_date;
        $driver->license_no             =   $request->license_no;
        $driver->license_category_id    =   $request->license_category_id;
        $driver->license_expiry_date    =   $request->license_expiry_date;
        $driver->uniform_issue_date    =   $request->uniform_issue_date;
        $driver->sandal_issue_date    =   $request->sandal_issue_date;
        $driver->address                =   $request->address;
        
        $cnicFileName = null;
        if ($request->hasFile('cnic_file')) {
            $cnic = $request->file('cnic_file');
            $cnicFileName = time() . '_cnic_file.' . $cnic->getClientOriginalExtension();
            $cnic->move($uploadPath, $cnicFileName);

            $driver->cnic_file  =   $cnicFileName;
        }

        $eobiCardFileName = null;
        if ($request->hasFile('eobi_card_file')) {
            $eobi = $request->file('eobi_card_file');
            $eobiCardFileName = time() . '_eobi_card_file.' . $eobi->getClientOriginalExtension();
            $eobi->move($uploadPath, $eobiCardFileName);

            $driver->eobi_card_file =   $eobiCardFileName;
        }

        $pictureFileName = null;
        if ($request->hasFile('picture_file')) {
            $picture = $request->file('picture_file');
            $pictureFileName = time() . '_picture_file.' . $picture->getClientOriginalExtension();
            $picture->move($uploadPath, $pictureFileName);

            $driver->picture_file   =   $pictureFileName;
        }

        $medicalReportFileName = null;
        if ($request->hasFile('medical_report_file')) {
            $medicalReport = $request->file('medical_report_file');
            $medicalReportFileName = time() . '_medical_report_file.' . $medicalReport->getClientOriginalExtension();
            $medicalReport->move($uploadPath, $medicalReportFileName);

            $driver->medical_report_file    =   $medicalReportFileName;
        }

        $authorityLetterFileName = null;
        if ($request->hasFile('authority_letter_file')) {
            $authorityLetter = $request->file('authority_letter_file');
            $authorityLetterFileName = time() . '_authority_letter_file.' . $authorityLetter->getClientOriginalExtension();
            $authorityLetter->move($uploadPath, $authorityLetterFileName);

            $driver->authority_letter_file  =   $authorityLetterFileName;
        }

        $employeeCardFileName = null;
        if ($request->hasFile('employee_card_file')) {
            $employeeCard = $request->file('employee_card_file');
            $employeeCardFileName = time() . '_employee_card_file.' . $employeeCard->getClientOriginalExtension();
            $employeeCard->move($uploadPath, $employeeCardFileName);

            $driver->employee_card_file =   $employeeCardFileName;
        }

        $ddcFileName = null;
        if ($request->hasFile('ddc_file')) {
            $ddc = $request->file('ddc_file');
            $ddcFileName = time() . '_ddc_file.' . $ddc->getClientOriginalExtension();
            $ddc->move($uploadPath, $ddcFileName);

            $driver->ddc_file   =   $ddcFileName;
        }

        $thirdPartyFileName = null;
        if ($request->hasFile('third_party_driver_file')) {
            $thirdParty = $request->file('third_party_driver_file');
            $thirdPartyFileName = time() . '_third_party_driver_file.' . $thirdParty->getClientOriginalExtension();
            $thirdParty->move($uploadPath, $thirdPartyFileName);

            $driver->third_party_driver_file    =   $thirdPartyFileName;
        }

        $licenseFileName = null;
        if ($request->hasFile('license_file')) {
            $license = $request->file('license_file');
            $licenseFileName = time() . '_license_file.' . $license->getClientOriginalExtension();
            $license->move($uploadPath, $licenseFileName);

            $driver->license_file   =   $licenseFileName;
        }

        $driver->save();

        return redirect()->route('admin.drivers.index')->with('success', 'Drive updated successfully.');
    }




    public function show(Driver $driver)
    {
        return view('admin.drivers.show', compact('driver'));
    }

    public function destroy(Driver $driver)
    {
        $driver->is_active = 0;
        $driver->save();

        return redirect()->route('admin.drivers.index')->with('delete_msg', 'Driver deleted successfully.');
    }
    
    public function destroyMultiple(Request $request)
    {
        $ids = $request->ids;
        Driver::whereIn('id', $ids)->update(['is_active' => 0]);
        return response()->json(['success' => true]);
    }
}
