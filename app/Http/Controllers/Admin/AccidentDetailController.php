<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccidentDetail;
use App\Models\AccidentDetailFile;
use Illuminate\Http\Request;

class AccidentDetailController extends Controller
{

    public function __construct()
    {

        if (!auth()->user()->hasPermission('accident_details')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index(Request $request)
    {
        $status = $request->query('payment_status', 'all');
        $search = $request->query('search', '');
        $per_page = (int)$request->query('per_page', 10);

        $payment_statuses = array(
            'pending' => 'Pending',
            'received' => 'Received',
        );

        $query = AccidentDetail::query();

        // Filter by payment status
        if ($status !== 'all' && array_key_exists($status, $payment_statuses)) {
            $query->where('payment_status', $status);
        }

        // Search across multiple fields
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('accident_id', 'like', "%{$search}%")
                    ->orWhere('vehicle_no', 'like', "%{$search}%")
                    ->orWhere('workshop', 'like', "%{$search}%");
            });
        }

        $accidentDetails = $query->paginate($per_page);

        return view('admin.accidentDetails.index', compact('accidentDetails', 'payment_statuses', 'status'));
    }

    public function create()
    {
        $accident_id = AccidentDetail::GetAccidentId();
        $vehicles = \App\Models\Vehicle::pluck('vehicle_no', 'vehicle_no')->toArray();
        $payment_statuses = array(
            'pending' => 'Pending',
            'received' => 'Received',
        );
        return view('admin.accidentDetails.create', compact('accident_id', 'vehicles', 'payment_statuses'));
    }

    public function store(Request $request)
    {
        // the image should have been resized before upload
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_no' => 'required',
                'workshop' => 'required',
                'third_party' => 'required',
                'claim_amount' => 'required|integer',
                'depreciation_amount' => 'required|integer',
                'remarks' => 'required',
                'payment_status' => 'required',
                'fill_image' => 'nullable|array',
                'fill_image.*' => 'file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            ],
            [
                'vehicle_no.required' => 'Vehicle is required',
                'workshop.required' => 'Workshop is required',
                'third_party.required' => 'Third party is required',
                'claim_amount.required' => 'Claim Amount is required',
                'claim_amount.integer' => 'Claim Amount must be an integer',
                'depreciation_amount.required' => 'Depreciation Amount is required',
                'depreciation_amount.integer' => 'Depreciation Amount must be an integer',
                'remarks.required' => 'Remarks is required',
                'payment_status.required' => 'Payment Status is required',
                'fill_image.*.file' => 'Each file must be valid',
                'fill_image.*.mimes' => 'Files must be jpeg, png, jpg, gif or pdf',
                'fill_image.*.max' => 'Each file must not exceed 5MB',
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $accidentDetail = new AccidentDetail();
        $accidentDetail->accident_id = $request->accident_id;
        $accidentDetail->vehicle_no = $request->vehicle_no;
        $accidentDetail->insurance = $request->insurance;
        $accidentDetail->ownership = $request->ownership;
        $accidentDetail->driver_name = $request->driver_name;
        $accidentDetail->license_no = $request->license_no;
        $accidentDetail->policy_no = $request->policy_no;
        $accidentDetail->workshop = $request->workshop;
        $accidentDetail->third_party = $request->third_party;
        $accidentDetail->claim_amount = $request->claim_amount;
        $accidentDetail->depreciation_amount = $request->depreciation_amount;
        $accidentDetail->remarks = $request->remarks;
        $accidentDetail->bill_to_ke = $request->has('bill_to_ke') ? 1 : 0;
        $accidentDetail->payment_status = $request->payment_status;
        $accidentDetail->created_by = auth()->id();
        $accidentDetail->save();

        // Handle file uploads
        if ($request->hasFile('fill_image')) {
            foreach ($request->file('fill_image') as $file) {
                // Get file size before moving
                $fileSize = $file->getSize();
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $extension;

                $file->move(public_path('uploads/accident_details'), $filename);

                // Determine file type
                $fileType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'document';

                // Save file record to database
                AccidentDetailFile::create([
                    'accident_detail_id' => $accidentDetail->id,
                    'file_name' => $filename,
                    'file_path' => 'uploads/accident_details/' . $filename,
                    'file_type' => $fileType,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $fileSize,
                ]);
            }
        }

        return redirect()->route('accidentDetails.index')->with('success', 'Accident Details created successfully.');
    }

    public function edit(AccidentDetail $accidentDetail)
    {
        $vehicles = \App\Models\Vehicle::pluck('vehicle_no', 'vehicle_no')->toArray();
        $payment_statuses = array(
            'pending' => 'Pending',
            'received' => 'Received',
        );
        return view('admin.accidentDetails.edit', compact('accidentDetail', 'vehicles', 'payment_statuses'));
    }

    public function update(Request $request, AccidentDetail $accidentDetail)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_no' => 'required',
                'workshop' => 'required',
                'third_party' => 'required',
                'claim_amount' => 'required|integer',
                'depreciation_amount' => 'required|integer',
                'remarks' => 'required',
                'payment_status' => 'required',
                'fill_image' => 'nullable|array',
                'fill_image.*' => 'file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            ],
            [
                'vehicle_no.required' => 'Vehicle is required',
                'workshop.required' => 'Workshop is required',
                'third_party.required' => 'Third party is required',
                'claim_amount.required' => 'Claim Amount is required',
                'claim_amount.integer' => 'Claim Amount must be an integer',
                'depreciation_amount.required' => 'Depreciation Amount is required',
                'depreciation_amount.integer' => 'Depreciation Amount must be an integer',
                'remarks.required' => 'Remarks is required',
                'payment_status.required' => 'Payment Status is required',
                'fill_image.*.file' => 'Each file must be valid',
                'fill_image.*.mimes' => 'Files must be jpeg, png, jpg, gif or pdf',
                'fill_image.*.max' => 'Each file must not exceed 5MB',
            ]
        );
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $accidentDetail->vehicle_no = $request->vehicle_no;
        $accidentDetail->insurance = $request->insurance;
        $accidentDetail->ownership = $request->ownership;
        $accidentDetail->driver_name = $request->driver_name;
        $accidentDetail->license_no = $request->license_no;
        $accidentDetail->policy_no = $request->policy_no;
        $accidentDetail->workshop = $request->workshop;
        $accidentDetail->third_party = $request->third_party;
        $accidentDetail->claim_amount = $request->claim_amount;
        $accidentDetail->depreciation_amount = $request->depreciation_amount;
        $accidentDetail->remarks = $request->remarks;
        $accidentDetail->bill_to_ke = $request->has('bill_to_ke') ? 1 : 0;
        $accidentDetail->payment_status = $request->payment_status;
        $accidentDetail->save();

        // Handle file uploads
        if ($request->hasFile('fill_image')) {
            foreach ($request->file('fill_image') as $file) {
                // Get file size before moving
                $fileSize = $file->getSize();
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $extension;

                $file->move(public_path('uploads/accident_details'), $filename);

                // Determine file type
                $fileType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'document';

                // Save file record to database
                AccidentDetailFile::create([
                    'accident_detail_id' => $accidentDetail->id,
                    'file_name' => $filename,
                    'file_path' => 'uploads/accident_details/' . $filename,
                    'file_type' => $fileType,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $fileSize,
                ]);
            }
        }

        return redirect()->route('accidentDetails.index')->with('success', 'Accident Details updated successfully.');
    }

    public function show(AccidentDetail $accidentDetail)
    {
        $payment_statuses = array(
            'pending' => 'Pending',
            'received' => 'Received',
        );
        return view('admin.accidentDetails.show', compact('accidentDetail', 'payment_statuses'));
    }

    public function destroy(AccidentDetail $accidentDetail)
    {
        $accidentDetail->is_active = 0;
        $accidentDetail->save();
        return redirect()->route('accidentDetails.index')->with('delete_msg', 'Accident Details deleted successfully.');
    }

    public function getVehicleInfo($vehicleNo)
    {
        $vehicle = \App\Models\Vehicle::where('vehicle_no', $vehicleNo)->first();

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        // Get last driver assigned to vehicle
        $driver = $vehicle->drivers()->latest()->first();

        return response()->json([
            'vehicle_no' => $vehicle->vehicle_no,
            'insurance' => $vehicle->insurance_company_id ? \App\Models\InsuranceCompany::find($vehicle->insurance_company_id)->name ?? '' : '',
            'ownership' => $vehicle->ownership,
            'driver_name' => $driver ? $driver->full_name : '',
            'license_no' => $driver ? $driver->license_no : '',
            'policy_no' => $vehicle->insurance_policy_no,
        ]);
    }

    public function deleteFile($fileId)
    {
        $file = AccidentDetailFile::find($fileId);

        if (!$file) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        // Delete the physical file from storage
        $filePath = public_path($file->file_path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete the database record
        $file->delete();

        return response()->json(['success' => true, 'message' => 'File deleted successfully']);
    }
}
