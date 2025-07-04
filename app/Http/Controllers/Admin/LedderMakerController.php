<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LedderMaker;
use App\Models\City;

class LedderMakerController extends Controller
{
    public function index()
    {
        $makers = LedderMaker::latest()->get();
        return view('admin.ledder_makers.index', compact('makers'));
    }

    public function create()
    {
                $cities = City::with('country')->get();
        return view('admin.ledder_makers.create',compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'maker_name' => 'required|string',
            'city' => 'required|string',
        ]);

        LedderMaker::create($request->all());

        return redirect()->route('admin.ledder_makers.index')->with('success', 'Ledder Maker created successfully.');
    }

    public function show(LedderMaker $ledderMaker)
    {
        return view('admin.ledder_makers.show', compact('ledderMaker'));
    }

    public function edit(LedderMaker $ledder_maker)
    {
                        $cities = City::with('country')->get();
        return view('admin.ledder_makers.edit', compact('ledder_maker','cities'));
    }

    public function update(Request $request, LedderMaker $ledder_maker)
    {
        $request->validate([
            'maker_name' => 'required|string',
            'city' => 'required|string',
        ]);

        $ledder_maker->update($request->all());

        return redirect()->route('admin.ledder_makers.index')->with('success', 'Ledder Maker updated successfully.');
    }

    public function destroy(LedderMaker $ledder_maker)
    {
        $ledder_maker->delete();
        return redirect()->route('admin.ledder_makers.index')->with('success', 'Ledder Maker deleted.');
    }
}
