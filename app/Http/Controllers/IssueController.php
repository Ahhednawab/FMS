<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $issues = Issue::orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.issues.index', compact('issues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('admin.issues.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        // Add created_by to validated data
        $validated['created_by'] = auth()->id();

        Issue::create($validated);

        return redirect()
            ->route('issues.index')
            ->with('success', 'Issue created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {

        $issue = Issue::findOrFail($id);

        return view('admin.issues.show', compact('issue'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {

        $issue = Issue::findOrFail($id);

        return view('admin.issues.edit', compact('issue'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'is_active'      => 'required|string',
        ]);

        $issue = Issue::findOrFail($id);
        $issue->update($validated);

        return redirect()
            ->route('issues.index')
            ->with('success', 'Issue updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();

        return redirect()
            ->route('issues.index')
            ->with('success', 'Issue deleted successfully.');
    }
}
