<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use App\Traits\DraftTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DraftController extends Controller
{
    use DraftTrait;
    public function index()
    {
        $drafts = Draft::where('created_by', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.drafts.index', compact('drafts'));
    }

    public function edit(Draft $draft)
    {
        // Ensure user can only edit their own drafts
        if ($draft->created_by !== Auth::id()) {
            abort(403);
        }

        // Map module to create route
        $routeMap = [
            'users' => 'admin.users.create',
            'cities' => 'admin.cities.create',
            'stations' => 'admin.stations.create',
            'ibc_centers' => 'admin.ibcCenters.create',
            'warehouses' => 'admin.warehouses.create',
            'vehicles' => 'admin.vehicles.create',
            'drivers' => 'admin.drivers.create',
            'vendors' => 'admin.vendors.create',
            'inventory_demands' => 'admin.inventoryDemands.create',
        ];

        $route = $routeMap[$draft->module] ?? null;
        
        if (!$route) {
            abort(404, 'Module not found');
        }

        return redirect()->route($route, ['draft_id' => $draft->id]);
    }

    public function destroy(Draft $draft)
    {
        // Ensure user can only delete their own drafts
        if ($draft->created_by !== Auth::id()) {
            abort(403);
        }

        // Clean up files before deleting draft
        if ($draft->file_info) {
            $this->cleanupDraftFiles($draft->file_info);
        }

        $draft->delete();

        return redirect()->route('admin.drafts.index')->with('success', 'Draft deleted successfully.');
    }

    public function downloadFile($path)
    {
        // Decode the path parameter
        $filePath = base64_decode($path);
        
        // Ensure the file exists and is within uploads directory
        if (!str_starts_with($filePath, 'uploads/') || !file_exists(public_path($filePath))) {
            abort(404, 'File not found');
        }
        
        // Get file info
        $fullPath = public_path($filePath);
        $filename = basename($filePath);
        
        return response()->download($fullPath, $filename);
    }

    public function viewFile($path)
    {
        // Decode the path parameter
        $filePath = base64_decode($path);
        
        // Ensure the file exists and is within uploads directory
        if (!str_starts_with($filePath, 'uploads/') || !file_exists(public_path($filePath))) {
            abort(404, 'File not found');
        }
        
        // Get file info
        $fullPath = public_path($filePath);
        $mimeType = mime_content_type($fullPath);
        
        // For images, show in browser; for others, download
        if (str_starts_with($mimeType, 'image/')) {
            return response()->file($fullPath);
        } else {
            return response()->download($fullPath);
        }
    }

    public function removeFile(Request $request, Draft $draft)
    {
        if ($draft->created_by !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $field = $request->input('field');
        if (!$field) {
            return response()->json(['message' => 'Field is required'], 422);
        }

        $fileInfo = is_array($draft->file_info) ? $draft->file_info : [];
        if (!isset($fileInfo[$field])) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Delete file from disk
        $path = public_path($fileInfo[$field]['path'] ?? '');
        if ($path && file_exists($path)) {
            @unlink($path);
        }

        // Remove from draft and save
        unset($fileInfo[$field]);
        $draft->file_info = $fileInfo;
        $draft->save();

        return response()->json(['message' => 'Removed', 'field' => $field]);
    }
}