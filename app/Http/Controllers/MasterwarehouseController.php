<?php

namespace App\Http\Controllers;

use App\Models\Masterwarehouse;
use Illuminate\Http\Request;

class MasterwarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('warehouse.dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Masterwarehouse $masterwarehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Masterwarehouse $masterwarehouse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Masterwarehouse $masterwarehouse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Masterwarehouse $masterwarehouse)
    {
        //
    }
}
