<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;

class CategoryController extends Controller
{
    public function __construct()
    {

        if (!auth()->user()->hasPermission('categories')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }
    public function index()
    {
        $categories = ProductCategory::where('is_active', 1)->orderby('id', 'DESC')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $serial_no = ProductCategory::GetSerialNumber();
        return view('admin.categories.create', compact('serial_no'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'            =>    'required|string|max:255',
            ],
            [
                'name.required'    =>    'Category Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = new ProductCategory();
        $category->serial_no = $request->serial_no;
        $category->name = $request->name;
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'name'            => 'required|string|max:255',
            ],
            [
                'name.required'    =>  'Category Name is required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category->name = $request->name;
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function show(ProductCategory $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    public function destroy(ProductCategory $category)
    {
        $category->is_active = 0;
        $category->save();

        return redirect()->route('categories.index')->with('delete_msg', 'Category deleted successfully.');
    }
}
