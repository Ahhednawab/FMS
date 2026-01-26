<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch notifications (AJAX)
     */
    public function index(Request $request)
    {
        $query = Notification::query()
            ->where('type', $request->type)
            ->where('is_read', false)
            ->orderByDesc('created_at');

        if ($request->filled('vehicle_id')) {
            $query->where('ref_id', $request->vehicle_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('ref_id', $request->driver_id);
        }

        if ($request->filled('title')) {
            $query->where('title', $request->title);
        }

        return response()->json([
            'data' => $query->paginate(10)
        ]);
    }


    public function driverAlerts()
    {
        return response()->json([
            'data' => Notification::where('type', Notification::TYPE_DRIVER)
                ->distinct()
                ->pluck('title')
        ]);
    }
    /**
     * Fetch distinct alerts (titles) for maintenance
     */
    public function maintenanceAlerts()
    {
        $alerts = Notification::where('type', 'maintenance')
            ->where('is_read', false)
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        return response()->json([
            'status' => 'success',
            'data'   => $alerts,
        ]);
    }

    public function masterDataAlerts()
    {
        $alerts = Notification::where('type', 'master_data')
            ->where('is_read', false)
            ->select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        return response()->json([
            'status' => 'success',
            'data'   => $alerts,
        ]);
    }

    /**
     * Mark notification as done
     */
    public function markAsDone($id)
    {
        Notification::where('id', $id)->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
        ]);
    }
}
