<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Designation;
use App\Models\DriverStatus;
use App\Models\Vehicle;
use App\Models\MaritalStatus;
use App\Models\LicenseCategory;


use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::with(['driverStatus','vehicle','maritalStatus','licenseCategory'])->where('is_active',1)->latest()->get();
        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $serial_no = Driver::GetSerialNumber();
        $driver_status = DriverStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        $vehicles = Vehicle::where('is_active', 1)
            ->whereNotIn('id', function ($query) {
                $query->select('vehicle_id')
                    ->from('drivers')
                    ->where('is_active', 1);
            })
            ->orderBy('vehicle_no')
            ->pluck('vehicle_no', 'id');

        $marital_status = MaritalStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $licence_category = LicenseCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        $status = array(
            'yes'   =>  'Yes',
            'no'    =>  'No',
        );
        return view('admin.drivers.create', compact('serial_no','driver_status','marital_status','licence_category','status','vehicles'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'full_name' =>  'required',
                'father_name' =>  'required',
                'mother_name' =>  'required',
                'phone' =>  'required|string|size:12',
                'salary' =>  'required|numeric',
                'account_no' =>  'required',
                'driver_status_id' =>  'required',
                'marital_status_id' =>  'required',
                'dob' =>  'required|date',
                'vehicle_id' =>  'required_if:driver_status_id,1|nullable',
                'cnic_no' =>  'required|string|size:15',
                'cnic_expiry_date' =>  'required|date',
                'cnic_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'eobi_no' =>  'required',
                // 'eobi_start_date' =>  'required',
                // 'eobi_card_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'picture_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'medical_report_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'authority_letter_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'employment_date' =>  'required|date',
                'employee_card_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'ddc_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'third_party_driver_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'license_no' =>  'required',
                'license_category_id' =>  'required',
                'license_expiry_date' =>  'required|date',
                'license_file' =>  'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'uniform_issue_date'    =>  'required|date',
                'sandal_issue_date'    =>  'required|date',
                'address' =>  'required',
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
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
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

        return redirect()->route('admin.drivers.index')->with('success', 'Driver created successfully.');
    }

    public function edit(Driver $driver){
        $driver_status = DriverStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        $vehicles = Vehicle::where('is_active', 1)
            ->whereNotIn('id', function ($query) use ($driver) {
                $query->select('vehicle_id')
                    ->from('drivers')
                    ->where('is_active', 1)
                    ->where('vehicle_id', '!=', $driver->vehicle_id);
            })
            ->orderBy('vehicle_no')
            ->pluck('vehicle_no', 'id');

        $marital_status = MaritalStatus::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $licence_category = LicenseCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        
        $status = array(
            'yes'   =>  'Yes',
            'no'    =>  'No',
        );
        return view('admin.drivers.edit', compact('driver','driver_status','marital_status','licence_category','status','vehicles'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'full_name' =>  'required',
                'father_name' =>  'required',
                'mother_name' =>  'required',
                'phone' =>  'required|string|size:12',
                'salary' =>  'required|numeric',
                'account_no' =>  'required',
                'driver_status_id' =>  'required',
                'marital_status_id' =>  'required',
                'dob' =>  'required|date',
                // 'vehicle_id' =>  'required',
                'vehicle_id' =>  'required_if:driver_status_id,1|nullable',
                'cnic_no' =>  'required|string|size:15',
                'cnic_expiry_date' =>  'required|date',
                'cnic_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'eobi_no' =>  'required',
                // 'eobi_start_date' =>  'required',
                // 'eobi_card_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'picture_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                // 'medical_report_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'authority_letter_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'employment_date' =>  'required|date',
                'employee_card_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'ddc_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'third_party_driver_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'license_no' =>  'required',
                'license_category_id' =>  'required',
                'license_expiry_date' =>  'required|date',
                'license_file' =>  'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'uniform_issue_date'    =>  'required|date',
                'sandal_issue_date'    =>  'required|date',
                'address' =>  'required',
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
                'vehicle_id.required_if'            => 'Vehicle Number is required.',
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
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
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
}
