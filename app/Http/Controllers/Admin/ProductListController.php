<?php

namespace App\Http\Controllers\Admin;

use App\Models\Unit;
use App\Models\Brand;
use App\Models\Vendor;
use App\Models\ProductList;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Controllers\Controller;


class ProductListController extends Controller
{
    public function __construct()
    {

        if (!auth()->user()->hasPermission('products')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index(Request $request)
    {

        $productList = ProductList::with(['productCategory', 'brand', 'unit'])->where('is_active', 1)->orderby('id', 'DESC')->get();
        return view('admin.productList.index', compact('productList'));
    }

    public function create(Request $request)
    {

        $serial_no = ProductList::GetSerialNumber();
        $productCategory = ProductCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $brands = Brand::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $units = Unit::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        $vendors = Vendor::where('is_active', 1)->get();

        return view('admin.productList.create', compact('serial_no', 'productCategory', 'brands', 'units', 'vendors'));
    }

    public function store(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'name'                    =>   'required',

                'product_category_id'   =>   'required',
                'brand_id'                =>     'required',
                'unit_id'                =>    'required',
            ],
            [
                'name.required'           =>  'Product Name is required',

                'product_category_id.required'           =>  'Category  is required',
                'brand_id.required'           =>  'Brand is required',
                'unit_id.required'           =>  'Unit is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = new ProductList();
        $product->serial_no    =    $request->serial_no;
        $product->name    =    $request->name;

        $product->product_category_id    =    $request->product_category_id;
        $product->brand_id    =    $request->brand_id;
        $product->unit_id    =    $request->unit_id;
        $product->save();

        return redirect()->route('productList.index')->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, ProductList $productList)
    {

        $productCategory = ProductCategory::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $brands = Brand::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $units = Unit::where('is_active', 1)->orderBy('name')->pluck('name', 'id');

        return view('admin.productList.edit', compact('productList', 'productCategory', 'brands', 'units'));
    }

    public function update(Request $request, $id)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'name'  =>  'required',
                'product_category_id'   =>  'required',
                'brand_id'  =>  'required',
                'unit_id'   =>  'required',
            ],
            [
                'name.required'           =>  'Product Name is required',
                'product_category_id.required'           =>  'Category  is required',
                'brand_id.required'           =>  'Brand is required',
                'unit_id.required'           =>  'Unit is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = ProductList::find($id);
        $product->name                  =   $request->name;
        $product->product_category_id   =   $request->product_category_id;
        $product->brand_id              =   $request->brand_id;
        $product->unit_id               =   $request->unit_id;
        $product->save();

        return redirect()->route('productList.index')->with('success', 'Product Updated successfully.');
    }

    public function show(Request $request, ProductList $productList)
    {
        return view('admin.productList.show', compact('productList'));
    }

    public function destroy(Request $request, $id)
    {
        $product = ProductList::find($id);
        $product->is_active = 0;
        $product->save();
        return redirect()->route('productList.index')->with('delete_msg', 'Product deleted successfully.');
    }
}
