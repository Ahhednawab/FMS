<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLargerReport;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InventoryLargerReportCategory;
use App\Models\TransactionType;
use App\Models\Supplier;
use App\Models\InventoryLargerReportStatus;


use Illuminate\Http\Request;

class InventoryLargerReportController extends Controller
{
    public function index(){
        $inventoryLargerReports = InventoryLargerReport::with(['product','warehouse','inventoryLargerReportCategory','transactionType','supplier','inventoryLargerReportStatus'])->where('is_active',1)->get();

        return view('admin.inventoryLargerReports.index', compact('inventoryLargerReports'));
    }

    public function create(){
        $report_id = InventoryLargerReport::GetReportId();
        $products = Product::where('is_active',1)->orderBy('product_name','ASC')->pluck('product_name','id');
        $warehouse = Warehouse::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $category = InventoryLargerReportCategory::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $transactionTypes = TransactionType::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $suppliers = Supplier::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $status = InventoryLargerReportStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        
        return view('admin.inventoryLargerReports.create',compact('report_id','warehouse','category','transactionTypes','suppliers','status','products'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date' => 'required',
                'product_id' => 'required',
                'warehouse_id' => 'required',
                'category' => 'required',
                'location' => 'required',
                'transaction_type' => 'required',
                'supplier_id' => 'required',
                'order_quantity' => 'required|numeric',
                'order_price' => 'required|numeric',
                'status' => 'required',
                'delievery_date' => 'required',
            ],
            [
                'report_date.required'      =>  'Report Date is required',
                'product_id.required'     =>  'Product Name is required',
                'warehouse_id.required'        =>  'Warehouse is required',
                'category.required'         =>  'Category is required',
                'location.required'         =>  'Location is required',
                'transaction_type.required' =>  'Transaction Type is required',
                'supplier_id.required'         =>  'Supplier is required',
                'order_quantity.required'   =>  'Order Quantity is required',
                'order_price.required'      =>  'Order Price is required',
                'status.required'           =>  'Status is required',
                'delievery_date.required'   =>  'Delivery Date is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inventoryLargerReport = new InventoryLargerReport();
        $inventoryLargerReport->report_id           =   $request->report_id;
        $inventoryLargerReport->report_date         =   $request->report_date;
        $inventoryLargerReport->product_id        =   $request->product_id;
        $inventoryLargerReport->warehouse_id           =   $request->warehouse_id;
        $inventoryLargerReport->category            =   $request->category;
        $inventoryLargerReport->location            =   $request->location;
        $inventoryLargerReport->transaction_type    =   $request->transaction_type;
        $inventoryLargerReport->supplier_id            =   $request->supplier_id;
        $inventoryLargerReport->order_quantity      =   $request->order_quantity;
        $inventoryLargerReport->order_price         =   $request->order_price;
        $inventoryLargerReport->status              =   $request->status;
        $inventoryLargerReport->delievery_date      =   $request->delievery_date;
        $inventoryLargerReport->save();

        return redirect()->route('admin.inventoryLargerReports.index')->with('success', 'Inventory Larger Report created successfully.');
    }

    public function edit(InventoryLargerReport $inventoryLargerReport)
    {
        $products = Product::where('is_active',1)->orderBy('product_name','ASC')->pluck('product_name','id');
        $warehouse = Warehouse::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $category = InventoryLargerReportCategory::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $transactionTypes = TransactionType::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $suppliers = Supplier::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        $status = InventoryLargerReportStatus::where('is_active',1)->orderBy('name','ASC')->pluck('name','id');
        return view('admin.inventoryLargerReports.edit',compact('inventoryLargerReport','warehouse','category','transactionTypes','suppliers','status','products'));
    }

    public function update(Request $request, InventoryLargerReport $inventoryLargerReport)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'report_date' => 'required',
                'product_id' => 'required',
                'warehouse_id' => 'required',
                'category' => 'required',
                'location' => 'required',
                'transaction_type' => 'required',
                'supplier_id' => 'required',
                'order_quantity' => 'required|numeric',
                'order_price' => 'required|numeric',
                'status' => 'required',
                'delievery_date' => 'required',
            ],
            [
                'report_date.required'      =>  'Report Date is required',
                'product_id.required'     =>  'Product Name is required',
                'warehouse_id.required'        =>  'Warehouse is required',
                'category.required'         =>  'Category is required',
                'location.required'         =>  'Location is required',
                'transaction_type.required' =>  'Transaction Type is required',
                'supplier_id.required'         =>  'Supplier is required',
                'order_quantity.required'   =>  'Order Quantity is required',
                'order_price.required'      =>  'Order Price is required',
                'status.required'           =>  'Status is required',
                'delievery_date.required'   =>  'Delivery Date is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $inventoryLargerReport->report_date         =   $request->report_date;
        $inventoryLargerReport->product_id        =   $request->product_id;
        $inventoryLargerReport->warehouse_id           =   $request->warehouse_id;
        $inventoryLargerReport->category            =   $request->category;
        $inventoryLargerReport->location            =   $request->location;
        $inventoryLargerReport->transaction_type    =   $request->transaction_type;
        $inventoryLargerReport->supplier_id            =   $request->supplier_id;
        $inventoryLargerReport->order_quantity      =   $request->order_quantity;
        $inventoryLargerReport->order_price         =   $request->order_price;
        $inventoryLargerReport->status              =   $request->status;
        $inventoryLargerReport->delievery_date      =   $request->delievery_date;
        $inventoryLargerReport->save();

        return redirect()->route('admin.inventoryLargerReports.index')->with('success', 'Inventory Larger Report updated successfully.');
    }

    public function show(InventoryLargerReport $inventoryLargerReport)
    {
        return view('admin.inventoryLargerReports.show', compact('inventoryLargerReport'));
    }

    public function destroy(InventoryLargerReport $inventoryLargerReport)
    {
        $inventoryLargerReport->is_active = 0;
        $inventoryLargerReport->save();
        return redirect()->route('admin.inventoryLargerReports.index')->with('delete_msg', 'Inventory Larger Report deleted successfully.');
    }
}
