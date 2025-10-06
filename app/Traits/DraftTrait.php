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
                // Merge file_info into draftData so views can access $draftData['file_info']
                $draftData = is_array($draft->data) ? $draft->data : (array) ($draft->data ?? []);
                if (!empty($draft->file_info)) {
                    $draftData['file_info'] = $draft->file_info;
                }
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
        
        // Handle file uploads
        $filePaths = $this->handleDraftFileUploads($request, $module);
        // Files marked for removal (e.g., remove_file[fitness_file] = 1)
        $removeFlags = (array) $request->input('remove_file', []);
        
        if ($request->has('draft_id') && $request->draft_id) {
            // Update existing draft
            $draft = Draft::where('id', $request->draft_id)
                ->where('created_by', Auth::id())
                ->where('module', $module)
                ->first();
            
            if ($draft) {
                // Start from existing file_info
                $updatedFileInfo = is_array($draft->file_info) ? $draft->file_info : [];

                // Remove flagged files
                foreach ($removeFlags as $fieldName => $flag) {
                    if ($flag && isset($updatedFileInfo[$fieldName])) {
                        $this->cleanupDraftFiles([$updatedFileInfo[$fieldName]]);
                        unset($updatedFileInfo[$fieldName]);
                    }
                }

                // Add/replace with newly uploaded files
                foreach ($filePaths as $fieldName => $info) {
                    // If replacing, cleanup previous file for that field
                    if (isset($updatedFileInfo[$fieldName])) {
                        $this->cleanupDraftFiles([$updatedFileInfo[$fieldName]]);
                    }
                    $updatedFileInfo[$fieldName] = $info;
                }

                $draft->update([
                    'data' => $data,
                    'file_info' => $updatedFileInfo,
                ]);
            }
        } else {
            // Create new draft
            Draft::create([
                'created_by' => Auth::id(),
                'module' => $module,
                'data' => $data,
                'file_info' => $filePaths
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
            $draft = Draft::where('id', $request->draft_id)
                ->where('created_by', Auth::id())
                ->where('module', $module)
                ->first();
                
            if ($draft) {
                // Clean up files before deleting draft
                if ($draft->file_info) {
                    $this->cleanupDraftFiles($draft->file_info);
                }
                
                $draft->delete();
            }
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
                if (!empty($draft->file_info)) {
                    $draftData['file_info'] = $draft->file_info;
                }
                $draftId = $draft->id;
            }
        }
        
        return compact('draftData', 'draftId');
    }

    /**
     * Handle file uploads for drafts
     */
    protected function handleDraftFileUploads($request, $module)
    {
        $filePaths = [];
        
        // Ensure base draft directory exists under public/uploads/draft/{module}
        $draftBaseDir = public_path("uploads/draft/{$module}");
        if (!is_dir($draftBaseDir)) {
            @mkdir($draftBaseDir, 0775, true);
        }

        foreach ($request->allFiles() as $fieldName => $file) {
            if ($file && $file->isValid()) {
                // Capture metadata BEFORE moving (avoids SplFileInfo::getSize stat failed)
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $sizeBytes = $file->getSize();
                $mimeType = $file->getMimeType();

                // Generate unique filename (aligned with existing naming)
                $filename = time() . '_' . $fieldName . '.' . $extension;

                // Move into public/uploads/draft/{module}
                $file->move($draftBaseDir, $filename);

                $relativePath = "uploads/draft/{$module}/{$filename}";

                $filePaths[$fieldName] = [
                    'path' => $relativePath,
                    'original_name' => $originalName,
                    'size' => $sizeBytes,
                    'type' => $mimeType,
                    'uploaded_at' => now()->toDateTimeString()
                ];
            }
        }
        
        return $filePaths;
    }

    /**
     * Clean up draft files when draft is deleted
     */
    protected function cleanupDraftFiles($filePaths)
    {
        foreach ($filePaths as $fileInfo) {
            if (isset($fileInfo['path'])) {
                $fullPath = public_path($fileInfo['path']);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }
    }

    /**
     * Move existing draft files into a permanent module folder and return array of assigned filenames keyed by field
     */
    protected function finalizeDraftFiles(?int $draftId, string $module, string $permanentSubdir, array $fields): array
    {
        $assigned = [];
        if (!$draftId) { return $assigned; }
        $draft = \App\Models\Draft::where('id', $draftId)
            ->where('created_by', auth()->id())
            ->where('module', $module)
            ->first();
        if (!$draft || !is_array($draft->file_info)) { return $assigned; }

        $permanentDir = public_path($permanentSubdir);
        if (!file_exists($permanentDir)) {
            @mkdir($permanentDir, 0755, true);
        }
        foreach ($fields as $field) {
            $info = $draft->file_info[$field] ?? null;
            if (!$info || empty($info['path'])) { continue; }
            $src = public_path($info['path']);
            if (!file_exists($src)) { continue; }
            $ext = pathinfo($src, PATHINFO_EXTENSION);
            $filename = time() . '_' . $field . '.' . $ext;
            $dest = $permanentDir . DIRECTORY_SEPARATOR . $filename;
            @rename($src, $dest);
            if (!file_exists($dest)) {
                @copy($src, $dest);
                @unlink($src);
            }
            if (file_exists($dest)) {
                $assigned[$field] = $filename;
            }
        }
        return $assigned;
    }
}
