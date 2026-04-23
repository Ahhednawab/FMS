<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use App\Models\Driver;
use App\Models\IbcCenter;
use App\Models\InsuranceCompany;
use App\Models\LadderMaker;
use App\Models\ShiftHours;
use App\Models\ShiftTimings;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Vendor;
use App\Services\VehicleDriverAssignmentService;
use App\Services\VehicleMaintenanceScheduleService;
use App\Traits\DraftTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VehicleController extends Controller
{
    use DraftTrait;

    public function __construct(
        private VehicleDriverAssignmentService $vehicleDriverAssignmentService,
        private VehicleMaintenanceScheduleService $vehicleMaintenanceScheduleService
    ) {
        if (! auth()->user()->hasPermission('vehicles')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index()
    {
        $vehicles = Vehicle::with([
            'vehicleType',
            'station',
            'ibcCenter',
            'fabricationVendor',
            'shiftHours',
            'shiftTiming',
            'primaryDriver',
            'currentDriver',
            'poolDrivers',
        ])
            ->where('is_active', 1)
            ->orderby('id', 'DESC')
            ->get();

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create(Request $request)
    {
        $serial_no = Vehicle::GetSerialNumber();
        $insurance_companies = InsuranceCompany::where('is_active', 1)->get();
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
        $vendors = Vendor::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $shift_hours = ShiftHours::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $shift_timings = ShiftTimings::whereNotIn('id', [1, 2])->pluck('name', 'id');
        $regularDrivers = $this->getAssignableDrivers('regular');
        $poolDrivers = $this->getAssignableDrivers('pool');

        $status = [
            '1' => 'Yes',
            '2' => 'No',
        ];
        $draftInfo = $this->getDraftDataForView($request, 'vehicles');

        return view('admin.vehicles.create', compact('serial_no', 'insurance_companies', 'vehicleTypes', 'stations', 'status', 'ladder_maker', 'ibc_center', 'vendors', 'shift_hours', 'shift_timings', 'regularDrivers', 'poolDrivers') + $draftInfo);
    }

    public function store(Request $request)
    {

        // Handle draft saving
        if ($this->handleDraftSave($request, 'vehicles')) {
            return redirect()->back()->with('success', 'Draft saved successfully!');
        }

        $draftAttached = [
            'registration_file' => false,
            'fitness_file' => false,
            'insurance_file' => false,
            'route_permit_file' => false,
            'tax_file' => false,
        ];
        $draft = null;
        if ($request->filled('draft_id')) {
            $draft = \App\Models\Draft::where('id', $request->draft_id)
                ->where('created_by', auth()->id())
                ->where('module', 'vehicles')
                ->first();
            if ($draft && is_array($draft->file_info)) {
                foreach ($draftAttached as $field => $v) {
                    $draftAttached[$field] = isset($draft->file_info[$field]);
                }
            }
        }

        $rules = [
            'vehicle_no' => 'required|unique:vehicles,vehicle_no',
            'make' => 'required',
            'model' => 'required',
            'chasis_no' => 'required',
            'engine_no' => 'required',
            'ownership' => 'required',
            'pool_vehicle' => 'required|in:1,0',
            'primary_driver_id' => 'required|exists:drivers,id',
            'pool_driver_ids' => 'nullable|array',
            'pool_driver_ids.*' => 'exists:drivers,id|different:primary_driver_id',
            'shift_hour_id' => 'required',
            'vehicle_type_id' => 'required',
            'cone' => 'nullable|numeric|min:0',
            'station_id' => 'required',
            'ibc_center_id' => 'required_if:pool_vehicle,0',
            'medical_box' => 'nullable',
            'on_duty_status' => 'required',
            'seat_cover' => 'nullable',
            'fire_extenguisher' => 'nullable',
            'tracker_installation_date' => 'required|date',
            'is_new_vehicle' => 'required|boolean',
            'inspection_date' => 'required|date',
            'next_inspection_date' => 'required|date|after:inspection_date',
            'induction_date' => 'required|date',
            'pso_card' => 'required',
            'akpl' => 'required',
            ' insurance_policy_no' => 'nullable|string',
            'shift_timing_id' => 'required|exists:shift_timing,id',
            'registration_file' => ($draftAttached['registration_file'] ? 'nullable' : 'required').'|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'fitness_date' => 'required|date',
            'next_fitness_date' => 'required|date|after:fitness_date',
            'fitness_file' => ($draftAttached['fitness_file'] ? 'nullable' : 'required').'|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'insurance_policy_no' => 'nullable|string',

            'insurance_company_id' => 'required',
            'insurance_date' => 'required|date',
            'insurance_expiry_date' => 'required|date|after:insurance_date',
            'insurance_file' => ($draftAttached['insurance_file'] ? 'nullable' : 'required').'|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'route_permit_date' => 'required|date',
            'route_permit_expiry_date' => 'required|date|after:route_permit_date',
            'route_permit_file' => ($draftAttached['route_permit_file'] ? 'nullable' : 'required').'|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'tax_date' => 'required|date',
            'next_tax_date' => 'required|date|after:tax_date',
            'tax_file' => ($draftAttached['tax_file'] ? 'nullable' : 'required').'|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];

        $validator = \Validator::make(
            $request->all(),
            $rules,
            [
                'vehicle_no.required' => 'Vehicle No is required',
                'vehicle_no.unique' => 'This vehicle number already exists.',
                'make.required' => 'Make is required',
                'model.required' => 'Model is required',
                'chasis_no.required' => 'Chasis No is required',
                'engine_no.required' => 'Engine No is required',
                'ownership.required' => 'Ownership is required',
                'pool_vehicle.required' => 'Pool Vehicle is required',
                'primary_driver_id.required' => 'Primary Driver is required',
                'on_duty_status.required' => 'On Duty Status is required',
                'shift_hour_id.required' => 'Shift Hours is required',
                'vehicle_type_id.required' => 'Vehicle type is required',
                'cone.required' => 'Cone is required',
                'station_id.required' => 'Station is required',
                'ibc_center_id.required_if' => 'IBC Center is required',
                'seat_cover.required' => 'Seat Cover is required',
                'fire_extenguisher.required' => 'Fire Extinguisher is required',
                'tracker_installation_date.required' => 'Tracker Installation Date is required',
                'inspection_date.required' => 'Inspection Date is required',
                'next_inspection_date.required' => 'Next Inspection Date is required',
                'induction_date.required' => 'Induction Date is required',
                'pso_card.required' => 'PSO Card Details is required',
                'akpl.required' => 'AKPL is required',
                'registration_file.required' => 'Registration Attachment is required',
                'fitness_date.required' => 'Fitness Date is required',
                'next_fitness_date.required' => 'Next fitness date is required',
                'fitness_file.required' => 'Fitness Attachment is required',
                'insurance_company_id.required' => 'Insurance Company is required',
                'insurance_date.required' => 'Insurance Date is required',
                'insurance_expiry_date.required' => 'Insurance Expiry Date is required',
                'insurance_file.required' => 'Insurance Attachment is required',
                'route_permit_date.required' => 'Route Permit Date is required',
                'route_permit_expiry_date.required' => 'Route Permit Expiry Date is required',
                'route_permit_file.required' => 'Route Permit Attachment is required',
                'tax_date.required' => 'Tax Date is required',
                'next_tax_date.required' => 'Next Tax Date is required',
                'tax_file.required' => 'Tax Attachment is required',
            ]
        );
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('primary_driver_id')) {
                $primaryDriver = Driver::find($request->primary_driver_id);
                if (! $primaryDriver || $primaryDriver->driver_type !== 'regular') {
                    $validator->errors()->add('primary_driver_id', 'Primary driver must be a regular driver.');
                }
            }

            foreach ((array) $request->input('pool_driver_ids', []) as $poolDriverId) {
                $poolDriver = Driver::find($poolDriverId);
                if (! $poolDriver || $poolDriver->driver_type !== 'pool') {
                    $validator->errors()->add('pool_driver_ids', 'Pool drivers must be selected from pool driver records only.');
                    break;
                }
            }
        });
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/vehicles');

        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $vehicle = new Vehicle;
        $vehicle->serial_no = $request->serial_no;
        $vehicle->vehicle_no = $request->vehicle_no;
        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->chasis_no = $request->chasis_no;
        $vehicle->engine_no = $request->engine_no;
        $vehicle->ownership = $request->ownership;
        $vehicle->pool_vehicle = $request->pool_vehicle;
        $vehicle->is_new_vehicle = $request->boolean('is_new_vehicle');
        $vehicle->shift_hour_id = $request->shift_hour_id;
        $vehicle->vehicle_type_id = $request->vehicle_type_id;
        $vehicle->cone = $request->cone;
        $vehicle->station_id = $request->station_id;
        $vehicle->ibc_center_id = $request->ibc_center_id;
        $vehicle->fabrication_vendor_id = $request->fabrication_vendor_id;
        //        $vehicle->medical_box               =   $request->medical_box;
        $vehicle->on_duty_status = $request->on_duty_status;
        $vehicle->seat_cover = $request->seat_cover;
        $vehicle->fire_extenguisher = $request->fire_extenguisher;
        $vehicle->tracker_installation_date = $request->tracker_installation_date;
        $vehicle->inspection_date = $request->inspection_date;
        $vehicle->next_inspection_date = $request->next_inspection_date;
        $vehicle->induction_date = $request->induction_date;
        $vehicle->pso_card = $request->pso_card;
        $vehicle->akpl = $request->akpl;
        $vehicle->insurance_policy_no = $request->insurance_policy_no;
        $vehicle->shift_timing_id = $request->shift_timing_id;
        $vehicle->fitness_date = $request->fitness_date;
        $vehicle->next_fitness_date = $request->next_fitness_date;
        $vehicle->insurance_policy_no = $request->insurance_policy_no;
        $vehicle->insurance_company_id = $request->insurance_company_id;
        $vehicle->insurance_date = $request->insurance_date;
        $vehicle->insurance_expiry_date = $request->insurance_expiry_date;
        $vehicle->route_permit_date = $request->route_permit_date;
        $vehicle->route_permit_expiry_date = $request->route_permit_expiry_date;
        $vehicle->tax_date = $request->tax_date;
        $vehicle->next_tax_date = $request->next_tax_date;
        $vehicle->save();
        $vehicle_id = $vehicle->id;

        $registrationFileName = null;
        if ($request->hasFile('registration_file')) {
            $picture = $request->file('registration_file');
            $registrationFileName = time().'_registration_file.'.$picture->getClientOriginalExtension();
            $picture->move($uploadPath, $registrationFileName);
        }

        $fitnessFileName = null;
        if ($request->hasFile('fitness_file')) {
            $authority = $request->file('fitness_file');
            $fitnessFileName = time().'_fitness_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $fitnessFileName);
        }

        $insuranceFileName = null;
        if ($request->hasFile('insurance_file')) {
            $authority = $request->file('insurance_file');
            $insuranceFileName = time().'_insurance_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $insuranceFileName);
        }

        $routePermitFileName = null;
        if ($request->hasFile('route_permit_file')) {
            $authority = $request->file('route_permit_file');
            $routePermitFileName = time().'_route_permit_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $routePermitFileName);
        }

        $taxFileName = null;
        if ($request->hasFile('tax_file')) {
            $authority = $request->file('tax_file');
            $taxFileName = time().'_tax_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $taxFileName);
        }

        $vehicle = Vehicle::find($vehicle_id);
        $vehicle->registration_file = $registrationFileName;
        $vehicle->fitness_file = $fitnessFileName;
        $vehicle->insurance_file = $insuranceFileName;
        $vehicle->route_permit_file = $routePermitFileName;
        $vehicle->tax_file = $taxFileName;

        // ================================
        // AUTO EXPIRY CALCULATION LOGIC
        // ================================

        // Insurance → 1 Year
        $vehicle->insurance_expiry_date = Carbon::parse($request->insurance_date)->addYear();

        // Inspection → 8 Months
        $vehicle->next_inspection_date = Carbon::parse($request->inspection_date)->addMonths(8);

        // Tax → 1 Year
        $vehicle->next_tax_date = Carbon::parse($request->tax_date)->addYear();

        // Route Permit → 3 Years
        $vehicle->route_permit_expiry_date = Carbon::parse($request->route_permit_date)->addYears(3);

        // Fitness → conditional (new/old vehicle)
        if ($request->is_new_vehicle == 1) {
            $vehicle->next_fitness_date = Carbon::parse($request->fitness_date)->addMonths(6);
        } else {
            $vehicle->next_fitness_date = Carbon::parse($request->fitness_date)->addYear();
        }
        $vehicle->save();

        // If this submission came from a draft and some files were not re-uploaded,
        // move existing draft files to permanent uploads and attach to the vehicle
        if ($request->filled('draft_id')) {
            $draft = Draft::where('id', $request->draft_id)
                ->where('created_by', auth()->id())
                ->where('module', 'vehicles')
                ->first();
            if ($draft && is_array($draft->file_info)) {
                $map = [
                    'registration_file' => 'registration_file',
                    'fitness_file' => 'fitness_file',
                    'insurance_file' => 'insurance_file',
                    'route_permit_file' => 'route_permit_file',
                    'tax_file' => 'tax_file',
                ];
                $permanentDir = public_path('uploads/vehicles');
                if (! file_exists($permanentDir)) {
                    @mkdir($permanentDir, 0755, true);
                }
                $updated = false;
                foreach ($map as $field => $attr) {
                    // Skip if user uploaded a new file already
                    if (! empty($vehicle->{$attr})) {
                        continue;
                    }
                    $info = $draft->file_info[$field] ?? null;
                    if (! $info || empty($info['path'])) {
                        continue;
                    }
                    $draftFull = public_path($info['path']);
                    if (! file_exists($draftFull)) {
                        continue;
                    }
                    // Generate permanent filename preserving field extension
                    $ext = pathinfo($draftFull, PATHINFO_EXTENSION);
                    $filename = time().'_'.$field.'.'.$ext;
                    $dest = $permanentDir.DIRECTORY_SEPARATOR.$filename;
                    // Move file
                    @rename($draftFull, $dest);
                    if (! file_exists($dest)) {
                        // Fallback to copy if rename across volumes fails
                        @File::copy($draftFull, $dest);
                        @unlink($draftFull);
                    }
                    if (file_exists($dest)) {
                        $vehicle->{$attr} = $filename;
                        $updated = true;
                    }
                }
                if ($updated) {
                    $vehicle->save();
                }
            }
        }

        $this->vehicleMaintenanceScheduleService->ensureDefaults($vehicle);
        $this->vehicleDriverAssignmentService->syncAssignments(
            $vehicle,
            (int) $request->primary_driver_id,
            $request->input('pool_driver_ids', [])
        );

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'vehicles');

        return redirect()->route('vehicles.index')->with('success', 'Vehicle created successfully.');
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

        $shift_hours = ShiftHours::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $ladder_maker = LadderMaker::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $ibc_center = IbcCenter::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $vendors = Vendor::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $insurance_companies = InsuranceCompany::where('is_active', 1)->get();
        $shift_timings = ShiftTimings::whereNotIn('id', [1, 2])->pluck('name', 'id');
        $regularDrivers = $this->getAssignableDrivers('regular');
        $poolDrivers = $this->getAssignableDrivers('pool');

        $status = [
            '1' => 'Yes',
            '2' => 'No',
        ];
        $vehicle->loadMissing(['poolDrivers']);

        return view('admin.vehicles.edit', compact('vehicle', 'insurance_companies', 'vehicleTypes', 'stations', 'status', 'ladder_maker', 'ibc_center', 'vendors', 'shift_hours', 'shift_timings', 'regularDrivers', 'poolDrivers'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'vehicle_no' => [
                    'required',
                    // \Illuminate\Validation\Rule::unique('vehicles', 'vehicle_no')->ignore($vehicle->id),
                ],
                'make' => 'required',
                'model' => 'required',
                'chasis_no' => 'required',
                'engine_no' => 'required',
                'ownership' => 'required',
                'pool_vehicle' => 'required|in:1,0',
                'primary_driver_id' => 'required|exists:drivers,id',
                'pool_driver_ids' => 'nullable|array',
                'pool_driver_ids.*' => 'exists:drivers,id|different:primary_driver_id',
                'on_duty_status' => 'required',
                'is_new_vehicle' => 'required|boolean',
                'vehicle_type_id' => 'required',
                'cone' => 'nullable|numeric|min:0',
                'station_id' => 'required',
                'ibc_center_id' => 'required_if:pool_vehicle,0',
                'medical_box' => 'nullable',
                'seat_cover' => 'nullable',
                'fire_extenguisher' => 'nullable',
                'tracker_installation_date' => 'required|date',
                'inspection_date' => 'required|date',
                'next_inspection_date' => 'required|date|after:inspection_date',
                'induction_date' => 'required|date',
                'pso_card' => 'required',
                'akpl' => 'required',
                'shift_timing_id' => 'required|exists:shift_timing,id',
                'registration_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'fitness_date' => 'required|date',
                'next_fitness_date' => 'required|date|after:fitness_date',
                'fitness_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'insurance_policy_no' => 'nullable|string',
                'insurance_company_id' => 'required',
                'insurance_date' => 'required|date',
                'insurance_expiry_date' => 'required|date|after:insurance_date',
                'insurance_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'route_permit_date' => 'required|date',
                'route_permit_expiry_date' => 'required|date|after:route_permit_date',
                'route_permit_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
                'tax_date' => 'required|date',
                'next_tax_date' => 'required|date|after:tax_date',
                'tax_file' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            ],
            [
                'vehicle_no.required' => 'Vehicle No is required',
                // 'vehicle_no.unique'   => 'This vehicle number already exists.',
                'make.required' => 'Make is required',
                'model.required' => 'Model is required',
                'chasis_no.required' => 'Chasis No is required',
                'engine_no.required' => 'Engine No is required',
                'ownership.required' => 'Ownership is required',
                'pool_vehicle.required' => 'Pool Vehicle is required',
                'primary_driver_id.required' => 'Primary Driver is required',
                'on_duty_status.required' => 'On Duty Status is required',
                'vehicle_type_id.required' => 'Vehicle type is required',
                'cone.required' => 'Cone is required',
                'station_id.required' => 'Station is required',
                'ibc_center_id.required_if' => 'IBC Center is required',
                'seat_cover.required' => 'Seat Cover is required',
                'fire_extenguisher.required' => 'Fire Extinguisher is required',
                'tracker_installation_date.required' => 'Tracker Installation Date is required',
                'inspection_date.required' => 'Inspection Date is required',
                'next_inspection_date.required' => 'Next Inspection Date is required',
                'induction_date.required' => 'Induction Date is required',
                'pso_card.required' => 'PSO Card Details is required',
                'akpl.required' => 'AKPL is required',
                'registration_file.required' => 'Registration Attachment is required',
                'fitness_date.required' => 'Fitness Date is required',
                'next_fitness_date.required' => 'Next fitness date is required',
                'fitness_file.required' => 'Fitness Attachment is required',
                'insurance_company_id.required' => 'Insurance Company is required',
                'insurance_date.required' => 'Insurance Date is required',
                'insurance_expiry_date.required' => 'Insurance Expiry Date is required',
                'insurance_file.required' => 'Insurance Attachment is required',
                'route_permit_date.required' => 'Route Permit Date is required',
                'route_permit_expiry_date.required' => 'Route Permit Expiry Date is required',
                'route_permit_file.required' => 'Route Permit Attachment is required',
                'tax_date.required' => 'Tax Date is required',
                'next_tax_date.required' => 'Next Tax Date is required',
                'tax_file.required' => 'Tax Attachment is required',
            ]
        );
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('primary_driver_id')) {
                $primaryDriver = Driver::find($request->primary_driver_id);
                if (! $primaryDriver || $primaryDriver->driver_type !== 'regular') {
                    $validator->errors()->add('primary_driver_id', 'Primary driver must be a regular driver.');
                }
            }

            foreach ((array) $request->input('pool_driver_ids', []) as $poolDriverId) {
                $poolDriver = Driver::find($poolDriverId);
                if (! $poolDriver || $poolDriver->driver_type !== 'pool') {
                    $validator->errors()->add('pool_driver_ids', 'Pool drivers must be selected from pool driver records only.');
                    break;
                }
            }
        });
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadPath = public_path('uploads/vehicles');

        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // $vehicle->vehicle_no                =   $request->vehicle_no;
        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->chasis_no = $request->chasis_no;
        $vehicle->engine_no = $request->engine_no;
        $vehicle->ownership = $request->ownership;
        $vehicle->pool_vehicle = $request->pool_vehicle;
        $vehicle->is_new_vehicle = $request->boolean('is_new_vehicle');
        $vehicle->shift_hour_id = $request->shift_hour_id;
        $vehicle->vehicle_type_id = $request->vehicle_type_id;
        $vehicle->cone = $request->cone;
        $vehicle->station_id = $request->station_id;
        $vehicle->ibc_center_id = $request->ibc_center_id;
        $vehicle->fabrication_vendor_id = $request->fabrication_vendor_id;
        //        $vehicle->medical_box               =   $request->medical_box;
        $vehicle->on_duty_status = $request->on_duty_status;
        $vehicle->seat_cover = $request->seat_cover;
        $vehicle->fire_extenguisher = $request->fire_extenguisher;
        $vehicle->tracker_installation_date = $request->tracker_installation_date;
        $vehicle->inspection_date = $request->inspection_date;
        $vehicle->next_inspection_date = $request->next_inspection_date;
        $vehicle->induction_date = $request->induction_date;
        $vehicle->pso_card = $request->pso_card;
        $vehicle->akpl = $request->akpl;
        $vehicle->fitness_date = $request->fitness_date;
        $vehicle->next_fitness_date = $request->next_fitness_date;
        $vehicle->insurance_policy_no = $request->insurance_policy_no;
        $vehicle->insurance_company_id = $request->insurance_company_id;
        $vehicle->insurance_date = $request->insurance_date;
        $vehicle->insurance_expiry_date = $request->insurance_expiry_date;
        $vehicle->route_permit_date = $request->route_permit_date;
        $vehicle->route_permit_expiry_date = $request->route_permit_expiry_date;
        $vehicle->tax_date = $request->tax_date;
        $vehicle->next_tax_date = $request->next_tax_date;
        $vehicle->shift_timing_id = $request->shift_timing_id;

        $registrationFileName = null;
        if ($request->hasFile('registration_file')) {
            $picture = $request->file('registration_file');
            $registrationFileName = time().'_registration_file.'.$picture->getClientOriginalExtension();
            $picture->move($uploadPath, $registrationFileName);
            $vehicle->registration_file = $registrationFileName;
        }

        $fitnessFileName = null;
        if ($request->hasFile('fitness_file')) {
            $authority = $request->file('fitness_file');
            $fitnessFileName = time().'_fitness_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $fitnessFileName);
            $vehicle->fitness_file = $fitnessFileName;
        }

        $insuranceFileName = null;
        if ($request->hasFile('insurance_file')) {
            $authority = $request->file('insurance_file');
            $insuranceFileName = time().'_insurance_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $insuranceFileName);
            $vehicle->insurance_file = $insuranceFileName;
        }

        $routePermitFileName = null;
        if ($request->hasFile('route_permit_file')) {
            $authority = $request->file('route_permit_file');
            $routePermitFileName = time().'_route_permit_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $routePermitFileName);
            $vehicle->route_permit_file = $routePermitFileName;
        }

        $taxFileName = null;
        if ($request->hasFile('tax_file')) {
            $authority = $request->file('tax_file');
            $taxFileName = time().'_tax_file.'.$authority->getClientOriginalExtension();
            $authority->move($uploadPath, $taxFileName);
            $vehicle->tax_file = $taxFileName;
        }
        $vehicle->save();

        $this->vehicleMaintenanceScheduleService->ensureDefaults($vehicle);
        $this->vehicleDriverAssignmentService->syncAssignments(
            $vehicle,
            (int) $request->primary_driver_id,
            $request->input('pool_driver_ids', [])
        );

        return redirect()->route('vehicles.index')->with('success', 'Vehicle Updated successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'shiftTiming',
            'vehicleType',
            'fabricationVendor',
            'station',
            'ibcCenter',
            'primaryDriver',
            'currentDriver',
            'poolDrivers',
        ]);

        $register_on_portal = [
            '1' => 'Registered',
            '2' => 'Not Registered',
        ];

        $status = [
            '1' => 'Yes',
            '2' => 'No',
        ];

        return view('admin.vehicles.show', compact('vehicle', 'register_on_portal', 'status'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->is_active = 0;
        $vehicle->save();

        return redirect()->route('vehicles.index')->with('delete_msg', 'Vehicle deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $vehicleIds = $request->ids;
        foreach ($vehicleIds as $vehicleId) {
            $vehicle = Vehicle::find($vehicleId);
            $vehicle->is_active = 0;
            $vehicle->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully.',
        ]);
    }

    private function getAssignableDrivers(string $driverType)
    {
        return Driver::where('is_active', 1)
            ->where('driver_type', $driverType)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'vehicle_id', 'driver_type'])
            ->mapWithKeys(function (Driver $driver) {
                $label = $driver->full_name;
                if ($driver->vehicle_id && $driver->driver_type === 'regular') {
                    $label .= ' (Assigned)';
                }

                return [$driver->id => $label];
            });
    }
}
