<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{

    public function __construct()
    {

        if (!auth()->user()->hasPermission('brands')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index()
    {
        $brands = Brand::where('is_active', 1)->orderby('id', 'DESC')->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        $serial_no = Brand::GetSerialNumber();
        return view('admin.brands.create', compact('serial_no'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'            =>    'required|string|max:255',
            ],
            [
                'name.required'    =>    'Brand Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $brand = new Brand();
        $brand->serial_no = $request->serial_no;
        $brand->name = $request->name;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'            => 'required|string|max:255',
            ],
            [
                'name.required'    =>  'Brand Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $brand->name = $request->name;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function show(Brand $brand)
    {
        return view('admin.brands.show', compact('brand'));
    }

    public function destroy(Brand $brand)
    {
        $brand->is_active = 0;
        $brand->save();

        return redirect()->route('brands.index')->with('delete_msg', 'Brand deleted successfully.');
    }
}
