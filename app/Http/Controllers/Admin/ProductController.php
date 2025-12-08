<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductList;
use App\Models\ProductCategory;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['supplier', 'productCategory', 'product', 'warehouse'])->where('is_active', 1)->orderby('id', 'DESC')->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $serial_no = Product::GetSerialNumber();
        $warehouse = Warehouse::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $productList = ProductList::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $productCategory = ProductCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $suppliers = Vendor::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.products.create', compact('serial_no', 'warehouse', 'productList', 'productCategory', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'product_id'    =>  'required',
                'product_category_id'   =>  'required',
                'brands'    =>  'required',
                'quantity'            => 'required|numeric|min:1',
                'price'               => 'required|numeric|min:1',
                'restock_qty_alarm'   => 'required|numeric|min:1|lt:quantity',
                'supplier_id'    =>  'required',
                'warehouse_id'    =>  'required',
                'procured_date'    =>  'required|date',
                'expiry_date'   => 'nullable|date|after:procured_date',
            ],
            [
                'product_id.required'           =>  'Product is required',
                'product_category_id.required'  =>  'Category is required',
                'brands.required'               =>  'Brands is required',
                'quantity.required'             =>  'Quantity is required',
                'price.required'                =>  'Price is required',
                'restock_qty_alarm.required'   =>  'Restock Alarm Quantity is required',
                'supplier_id.required'          =>  'Supplier is required',
                'warehouse_id.required'         =>  'Warehouse is required',
                'procured_date.required'        =>  'Procured Date is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            dd($messages);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = new Product();
        $product->serial_no             =   $request->serial_no;
        $product->product_id            =   $request->product_id;
        $product->product_category_id   =   $request->product_category_id;
        $product->brands                =   $request->brands;
        $product->quantity              =   $request->quantity;
        $product->available             =   $request->quantity;
        $product->price                 =   $request->price;
        $product->restock_qty_alarm     =   $request->restock_qty_alarm;
        $product->supplier_id           =   $request->supplier_id;
        $product->warehouse_id          =   $request->warehouse_id;
        $product->procured_date         =   $request->procured_date;
        $product->expiry_date           =   $request->expiry_date;
        $product->description           =   $request->description;
        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $warehouse = Warehouse::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $productList = ProductList::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $productCategory = ProductCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $suppliers = Vendor::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.products.edit', compact('product', 'warehouse', 'productList', 'productCategory', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'product_id'    =>  'required',
                'product_category_id'   =>  'required',
                'brands'    =>  'required',
                'quantity'            => 'required|numeric|min:1',
                'price'               => 'required|numeric|min:1',
                'restock_qty_alarm'   => 'required|numeric|min:1|lt:quantity',
                'supplier_id'    =>  'required',
                'warehouse_id'    =>  'required',
                'procured_date'    =>  'required|date',
                'expiry_date'   => 'nullable|date|after:procured_date',
            ],
            [
                'product_id.required'           =>  'Product is required',
                'product_category_id.required'  =>  'Category is required',
                'brands.required'               =>  'Brands is required',
                'quantity.required'             =>  'Quantity is required',
                'price.required'                =>  'Price is required',
                'restock_qty_alarm.required'   =>  'Restock Alarm Quantity is required',
                'supplier_id.required'          =>  'Supplier is required',
                'warehouse_id.required'         =>  'Warehouse is required',
                'procured_date.required'        =>  'Procured Date is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product->product_id            =   $request->product_id;
        $product->product_category_id   =   $request->product_category_id;
        $product->brands                =   $request->brands;
        $product->quantity              =   $request->quantity;
        $product->available             =   $request->quantity;
        $product->price                 =   $request->price;
        $product->restock_qty_alarm     =   $request->restock_qty_alarm;
        $product->supplier_id           =   $request->supplier_id;
        $product->warehouse_id          =   $request->warehouse_id;
        $product->procured_date         =   $request->procured_date;
        $product->expiry_date           =   $request->expiry_date;
        $product->description           =   $request->description;
        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function destroy(Product $product)
    {
        $product->is_active = 0;
        $product->save();
        return redirect()->route('admin.products.index')->with('delete_msg', 'Product deleted successfully.');
    }

    public function getProductDetails(Request $request)
    {
        try {
            $productId = (int) $request->product_id;

            if ($productId <= 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid product ID',
                    'data'    => []
                ]);
            }

            $product = ProductList::with([
                'productCategory:id,name',
                'brand:id,name',
                'unit:id,name'
            ])->find($productId);


            if (!$product) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Product not found',
                    'data'    => []
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Product details fetched successfully',
                'data'    => $product
            ]);
        } catch (\Throwable $e) {

            // Log the real error (recommended)
            // \Log::error($e);

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong',
                'data'    => []
            ]);
        }
    }
}
