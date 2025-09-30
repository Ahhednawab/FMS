<?php

namespace App\Traits;

use App\Models\Draft;
use Illuminate\Support\Facades\Auth;

trait DraftTrait
{
    /**
     * Load draft data for a specific module
     */
    protected function loadDraftData($request, $module)
    {
        $draftData = [];
        $draftId = null;
        
        if ($request->has('draft_id')) {
            $draft = Draft::where('id', $request->draft_id)
                ->where('created_by', Auth::id())
                ->where('module', $module)
                ->first();
            
            if ($draft) {
                $draftData = $draft->data;
                $draftId = $draft->id;
            }
        }
        
        return compact('draftData', 'draftId');
    }

    /**
     * Handle draft saving logic
     */
    protected function handleDraftSave($request, $module)
    {
        if (!$request->has('save_draft')) {
            return false;
        }

        $data = $request->except(['_token', 'save_draft']);
        
        if ($request->has('draft_id') && $request->draft_id) {
            // Update existing draft
            $draft = Draft::where('id', $request->draft_id)
                ->where('created_by', Auth::id())
                ->where('module', $module)
                ->first();
            
            if ($draft) {
                $draft->update(['data' => $data]);
            }
        } else {
            // Create new draft
            Draft::create([
                'created_by' => Auth::id(),
                'module' => $module,
                'data' => $data,
            ]);
        }
        
        return true;
    }

    /**
     * Delete draft after successful creation
     */
    protected function deleteDraftAfterSuccess($request, $module)
    {
        if ($request->has('draft_id') && $request->draft_id) {
            Draft::where('id', $request->draft_id)
                ->where('created_by', Auth::id())
                ->where('module', $module)
                ->delete();
        }
    }

    /**
     * Get draft data for view
     */
    protected function getDraftDataForView($request, $module)
    {
        $draftData = [];
        $draftId = null;
        
        if ($request->has('draft_id')) {
            $draft = Draft::where('id', $request->draft_id)
                ->where('created_by', Auth::id())
                ->where('module', $module)
                ->first();
            
            if ($draft) {
                $draftData = $draft->data;
                $draftId = $draft->id;
            }
        }
        
        return compact('draftData', 'draftId');
    }
}
