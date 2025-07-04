<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryDemand;
use App\Models\User;
use App\Models\Department;
use App\Models\Priority;
use App\Models\InventoryStatus;
use App\Models\Product;
use App\Models\Warehouse;


class InventoryDemandController extends Controller
{
    public function index(){
        $inventoryDemands = InventoryDemand::with(['requestedBy','department','priority','inventoryStatus','product','warehouse'])->where('is_active',1)->get();
        
        return view('admin.inventoryDemands.index', compact('inventoryDemands'));
    }

    public function create(){
        $serial_no = InventoryDemand::GetSerialNumber();
        $users = User::where('designation_id',3)->where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $departments = Department::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $priority = Priority::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $status = InventoryStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $products = Product::where('is_active',1)->orderBy('product_name','ASC')->pluck('product_name','id');
        $warehouse = Warehouse::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        
        return view('admin.inventoryDemands.create',compact('serial_no','users','priority','status','warehouse','departments','products'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'request_date' => 'required',
                'requested_by' => 'required',
                'department_id' => 'required',
                'priority_id' => 'required',
                'status' => 'required',
                'product_id' => 'required',
                'warehouse_id' => 'required',
                'requested_qty' => 'required|numeric',
                'expected_delivery_date' => 'required',
            ],
            [
                'request_date.required'             =>  'Requested Date is required',
                'requested_by.required'             =>  'Requested By is required',
                'department_id.required'               =>  'Department is required',
                'priority_id.required'                 =>  'Priority is required',
                'status.required'                   =>  'Status is required',
                'product_id.required'             =>  'Product Name is required',
                'warehouse_id.required'                =>  'Warehouse is required',
                'requested_qty.required'            =>  'Requested Quantity is required',
                'expected_delivery_date.required'   =>  'Expected Delivery Date is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product_price = Product::where('id',$request->product_id)->first()->price;

        $inventoryDemand = new InventoryDemand();
        $inventoryDemand->serial_no                 =   $request->serial_no;
        $inventoryDemand->request_date              =   $request->request_date;
        $inventoryDemand->requested_by              =   $request->requested_by;
        $inventoryDemand->department_id                =   $request->department_id;
        $inventoryDemand->priority_id                  =   $request->priority_id;
        $inventoryDemand->status                    =   $request->status;
        $inventoryDemand->product_id              =   $request->product_id;
        $inventoryDemand->product_price             =   $product_price;
        $inventoryDemand->warehouse_id                 =   $request->warehouse_id;
        $inventoryDemand->requested_qty             =   $request->requested_qty;
        $inventoryDemand->expected_delivery_date    =   $request->expected_delivery_date;
        $inventoryDemand->save();

        return redirect()->route('admin.inventoryDemands.index')->with('success', 'Inventory Demand created successfully.');
    }

    public function edit(InventoryDemand $inventoryDemand)
    {
        $users = User::where('designation_id',3)->where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $departments = Department::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $priority = Priority::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $status = InventoryStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $products = Product::where('is_active',1)->orderBy('product_name','ASC')->pluck('product_name','id');
        $warehouse = Warehouse::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');

        return view('admin.inventoryDemands.edit',compact('inventoryDemand','users','priority','status','warehouse','departments','products'));
    }

    public function update(Request $request, InventoryDemand $inventoryDemand)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'request_date' => 'required',
                'requested_by' => 'required',
                'department_id' => 'required',
                'priority_id' => 'required',
                'status' => 'required',
                'product_id' => 'required',
                'warehouse_id' => 'required',
                'requested_qty' => 'required|numeric',
                'expected_delivery_date' => 'required',
            ],
            [
                'request_date.required'             =>  'Requested Date is required',
                'requested_by.required'             =>  'Requested By is required',
                'department_id.required'               =>  'Department is required',
                'priority_id.required'                 =>  'Priority is required',
                'status.required'                   =>  'Status is required',
                'product_id.required'             =>  'Product Name is required',
                'warehouse_id.required'                =>  'Warehouse is required',
                'requested_qty.required'            =>  'Requested Quantity is required',
                'expected_delivery_date.required'   =>  'Expected Delivery Date is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product_price = Product::where('id',$request->product_id)->first()->price;

        $inventoryDemand->request_date              =   $request->request_date;
        $inventoryDemand->requested_by              =   $request->requested_by;
        $inventoryDemand->department_id                =   $request->department_id;
        $inventoryDemand->priority_id                  =   $request->priority_id;
        $inventoryDemand->status                    =   $request->status;
        $inventoryDemand->product_id              =   $request->product_id;
        $inventoryDemand->product_price             =   $product_price;
        $inventoryDemand->warehouse_id                 =   $request->warehouse_id;
        $inventoryDemand->requested_qty             =   $request->requested_qty;
        $inventoryDemand->expected_delivery_date    =   $request->expected_delivery_date;
        $inventoryDemand->save();

        return redirect()->route('admin.inventoryDemands.index')->with('success', 'Inventory Demand updated successfully.');
    }

    public function show(InventoryDemand $inventoryDemand)
    {
        return view('admin.inventoryDemands.show', compact('inventoryDemand'));
    }

    public function destroy(InventoryDemand $inventoryDemand)
    {
        $inventoryDemand->is_active = 0;
        $inventoryDemand->save();   
        return redirect()->route('admin.inventoryDemands.index')->with('delete_msg', 'Inventory Demand deleted successfully.');
    }
}
