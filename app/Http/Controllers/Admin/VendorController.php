<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Models\City;
use App\Traits\DraftTrait;

class VendorController extends Controller
{
    use DraftTrait;
    public function index(){
    	$vendors = Vendor::with(['city','vendorType'])->where('is_active',1)->orderBy('id','DESC')->get();
    	return view('admin.vendors.index', compact('vendors'));
    }

    public function create(Request $request){
    	$serial_no = Vendor::GetSerialNumber();
        $vendor_types = VendorType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $cities = City::where('is_active',1)->orderBy('name','ASC')->get();
        
        $draftInfo = $this->getDraftDataForView($request, 'vendors');
        
    	return view('admin.vendors.create', compact('serial_no','vendor_types','cities') + $draftInfo);
    }

    public function store(Request $request)
    {
        // Handle draft saving
        if ($this->handleDraftSave($request, 'vendors')) {
            return redirect()->back()->with('success', 'Draft saved successfully!');
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'phone' => 'required|string|size:12',
                'vendor_type_id' => 'required',
                'city_id' => 'required',
                'description' => 'required',
            ],
            [
                'name.required'             =>  'Name is required',
                'phone.required'            =>  'Phone No is required',
                'phone.size'            =>  'Phone No must be 11 digits',
                'vendor_type_id.required'   =>  'Type is required',
                'city_id.required'          =>  'City is required',
                'description.required'      =>  'Description is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor = new Vendor();
        $vendor->serial_no      =   $request->serial_no;
        $vendor->name           =   $request->name;
        $vendor->phone          =   $request->phone;
        $vendor->vendor_type_id =   $request->vendor_type_id;
        $vendor->city_id        =   $request->city_id;
        $vendor->description    =   $request->description;
        $vendor->save();

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'vendors');

    	return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        $vendor_types = VendorType::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $cities = City::where('is_active',1)->orderBy('name','ASC')->get();
        return view('admin.vendors.edit', compact('vendor','vendor_types','cities'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'phone' => 'required|string|size:12',
                'vendor_type_id' => 'required',
                'city_id' => 'required',
                'description' => 'required',
            ],
            [
                'name.required'             =>  'Name is required',
                'phone.required'            =>  'Phone No is required',
                'phone.size'            =>  'Phone No must be 11 digits',
                'vendor_type_id.required'   =>  'Type is required',
                'city_id.required'          =>  'City is required',
                'description.required'      =>  'Description is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor->name           =   $request->name;
        $vendor->phone          =   $request->phone;
        $vendor->vendor_type_id =   $request->vendor_type_id;
        $vendor->city_id        =   $request->city_id;
        $vendor->description    =   $request->description;
        $vendor->save();

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor Updated successfully.');
    }

    public function show(Vendor $vendor)
    {
    	return view('admin.vendors.show', compact('vendor'));
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->is_active = 0;
        $vendor->save();
        return redirect()->route('admin.vendors.index')->with('delete_msg', 'Vendor deleted successfully.');
    }
}
