<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryDispatch;
use App\Models\Designation;
use App\Models\User;
use App\Models\Department;
use App\Models\DispatchTypes;
use App\Models\InventoryDispatchStatus;
use App\Models\Product;
use App\Models\Warehouse;

use Illuminate\Http\Request;

class InventoryDispatchController extends Controller
{
    public function index(){
        $inventoryDispatchs = InventoryDispatch::with(['dispatchBy','department','dispatchType','inventoryDispatchStatus','product','warehouse'])->where('is_active',1)->get();

        return view('admin.inventoryDispatchs.index', compact('inventoryDispatchs'));
    }

    public function create(){
        $serial_no = InventoryDispatch::GetSerialNumber();
        $users = User::where('designation_id',3)->where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $departments = Department::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $dispatchTypes = DispatchTypes::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $status = InventoryDispatchStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $products = Product::where('is_active',1)->orderBy('product_name','ASC')->pluck('product_name','id');
        $warehouse = Warehouse::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');

        
        return view('admin.inventoryDispatchs.create',compact('serial_no','users','dispatchTypes','status','warehouse','departments','products','warehouse'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'dispatch_date' => 'required',
                'dispatched_by' => 'required',
                'department_id' => 'required',
                'location' => 'required',
                'dispatch_type' => 'required',
                'status' => 'required',
                'product_id' => 'required',
                'order_price' => 'required|numeric',
                'warehouse_id' => 'required',
                'dispatched_qty' => 'required|numeric',
            ],
            [
                'dispatch_date.required'    =>  'Dispatch Date is required',
                'dispatched_by.required'    =>  'Dispatched By is required',
                'department_id.required'       =>  'Department is required',
                'location.required'         =>  'Location is required',
                'dispatch_type.required'    =>  'Dispatch Type is required',
                'status.required'           =>  'Status is required',
                'product_id.required'     =>  'Product Name is required',
                'order_price.required'      =>  'Order Price is required',
                'warehouse_id.required'        =>  'Warehouse is required',
                'dispatched_qty.required'   =>  'Dispatched Quantity is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inventoryDispatch = new InventoryDispatch();
        $inventoryDispatch->serial_no       = $request->serial_no;
        $inventoryDispatch->dispatch_date   = $request->dispatch_date;
        $inventoryDispatch->dispatched_by   = $request->dispatched_by;
        $inventoryDispatch->department_id      = $request->department_id;
        $inventoryDispatch->location        = $request->location;
        $inventoryDispatch->dispatch_type   = $request->dispatch_type;
        $inventoryDispatch->status          = $request->status;
        $inventoryDispatch->product_id    = $request->product_id;
        $inventoryDispatch->order_price     = $request->order_price;
        $inventoryDispatch->warehouse_id       = $request->warehouse_id;
        $inventoryDispatch->dispatched_qty  = $request->dispatched_qty;
        $inventoryDispatch->save();

        return redirect()->route('admin.inventoryDispatchs.index')->with('success', 'Inventory Dispatch created successfully.');
    }

    public function edit(Request $request, InventoryDispatch $inventoryDispatch)
    {
        $users = User::where('designation_id',3)->where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $departments = Department::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $dispatchTypes = DispatchTypes::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $status = InventoryDispatchStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $products = Product::where('is_active',1)->orderBy('product_name','ASC')->pluck('product_name','id');
        $warehouse = Warehouse::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        return view('admin.inventoryDispatchs.edit',compact('inventoryDispatch','users','dispatchTypes','status','warehouse','departments','products','warehouse'));
    }

    public function update(Request $request, InventoryDispatch $inventoryDispatch)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'dispatch_date' => 'required',
                'dispatched_by' => 'required',
                'department_id' => 'required',
                'location' => 'required',
                'dispatch_type' => 'required',
                'status' => 'required',
                'product_id' => 'required',
                'order_price' => 'required|numeric',
                'warehouse_id' => 'required',
                'dispatched_qty' => 'required|numeric',
            ],
            [
                'dispatch_date.required'    =>  'Dispatch Date is required',
                'dispatched_by.required'    =>  'Dispatched By is required',
                'department_id.required'       =>  'Department is required',
                'location.required'         =>  'Location is required',
                'dispatch_type.required'    =>  'Dispatch Type is required',
                'status.required'           =>  'Status is required',
                'product_id.required'     =>  'Product Name is required',
                'order_price.required'      =>  'Order Price is required',
                'warehouse_id.required'        =>  'Warehouse is required',
                'dispatched_qty.required'   =>  'Dispatched Quantity is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inventoryDispatch->dispatch_date   = $request->dispatch_date;
        $inventoryDispatch->dispatched_by   = $request->dispatched_by;
        $inventoryDispatch->department_id      = $request->department_id;
        $inventoryDispatch->location        = $request->location;
        $inventoryDispatch->dispatch_type   = $request->dispatch_type;
        $inventoryDispatch->status          = $request->status;
        $inventoryDispatch->product_id    = $request->product_id;
        $inventoryDispatch->order_price     = $request->order_price;
        $inventoryDispatch->warehouse_id       = $request->warehouse_id;
        $inventoryDispatch->dispatched_qty  = $request->dispatched_qty;
        $inventoryDispatch->save();

        return redirect()->route('admin.inventoryDispatchs.index')->with('success', 'Inventory Dispatch updated successfully.');
    }

    public function show(InventoryDispatch $inventoryDispatch)
    {
        return view('admin.inventoryDispatchs.show', compact('inventoryDispatch'));
    }

    public function destroy(InventoryDispatch $inventoryDispatch)
    {
        $inventoryDispatch->is_active = 0;
        $inventoryDispatch->save();  
        return redirect()->route('admin.inventoryDispatchs.index')->with('delete_msg', 'Inventory Dispatch deleted successfully.');
    }
}
