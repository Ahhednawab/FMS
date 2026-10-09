<?php

namespace App\Http\Controllers;

use App\Exports\AlertsExport;
use App\Models\Alert;
use App\Models\AlertVehicleStatus;
use App\Models\DailyMileageReport;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\AlertCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AlertController extends Controller
{
    /**
     * The alert list: every dashboard alert in one place, filtered by type.
     * The dashboard links here with ?type=... so the right list opens straight
     * away, and the filters can then be changed by hand.
     */
    public function index(Request $request, AlertCenterService $alertCenter)
    {
        $type = $alertCenter->normalizeType($request->query('type'));

        $filters = [
            'title' => (string) $request->query('title', ''),
            'reference_id' => (string) $request->query('reference_id', ''),
            'search' => (string) $request->query('search', ''),
            'page' => (int) $request->query('page', 1),
        ];

        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $referenceFilter = $alertCenter->referenceFilter($type);

        return view('alerts.index', [
            'alerts' => $alertCenter->paginate($type, $filters, $perPage)->withQueryString(),
            'types' => $alertCenter->types(),
            'type' => $type,
            'filters' => $filters,
            'perPage' => $perPage,
            'titleLabel' => $alertCenter->titleLabel($type),
            'titleOptions' => $alertCenter->titleOptions($type),
            'referenceFilter' => $referenceFilter,
            'referenceLabel' => $alertCenter->referenceLabel($type),
            'referenceOptions' => match ($referenceFilter) {
                'vehicle' => Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id')->all(),
                'driver' => Driver::where('is_active', 1)->orderBy('full_name')->pluck('full_name', 'id')->all(),
                default => [],
            },
        ]);
    }

    /** Excel export of the list on screen, with the same filters applied. */
    public function export(Request $request, AlertCenterService $alertCenter)
    {
        $type = $alertCenter->normalizeType($request->query('type'));

        $rows = $alertCenter->export($type, [
            'title' => (string) $request->query('title', ''),
            'reference_id' => (string) $request->query('reference_id', ''),
            'search' => (string) $request->query('search', ''),
        ]);

        $label = $alertCenter->types()[$type];

        return Excel::download(
            new AlertsExport($rows, $label, $alertCenter->referenceLabel($type)),
            Str::slug($label) . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /** Clear one alert, including the duplicate copies of it. */
    public function markDone(Request $request, AlertCenterService $alertCenter)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'reference_id' => 'nullable|integer',
        ]);

        $cleared = $alertCenter->markDone(
            $validated['type'],
            $validated['title'],
            $validated['reference_id'] ?? null
        );

        return back()->with('success', $cleared > 1
            ? 'Alert marked as done (' . $cleared . ' copies cleared).'
            : 'Alert marked as done.');
    }

    /** The alert master (title + mileage threshold) that used to be this page. */
    public function settings()
    {
        $alerts = Alert::paginate(10);

        return view('alerts.settings', compact('alerts'));
    }

    public function create()
    {
        return view('alerts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'threshold' => 'required|integer|min:0',
        ]);

        Alert::create($request->only('title', 'threshold'));


        $alerts = Alert::all();
        $vehicles = Vehicle::all();

        foreach ($alerts as $alert) {
            foreach ($vehicles as $vehicle) {

                $currentMileage = DailyMileageReport::where('vehicle_id', $vehicle->id)
                    ->orderByDesc('report_date')
                    ->value('current_km');


                AlertVehicleStatus::firstOrCreate(
                    [
                        'alert_id' => $alert->id,
                        'vehicle_id' => $vehicle->id,
                    ],
                    [
                        'last_mileage' => $currentMileage,
                    ]
                );
            }
        }

        return redirect()->route('alerts.settings')->with('success', 'Alert created successfully.');
    }


    public function edit(Alert $alert)
    {
        return view('alerts.edit', compact('alert'));
    }

    public function update(Request $request, Alert $alert)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'threshold' => 'required|integer|min:0',
        ]);

        $alert->update($request->only('title', 'threshold'));

        return redirect()->route('alerts.settings')->with('success', 'Alert updated successfully.');
    }

    public function destroy(Alert $alert)
    {
        $alert->delete();

        return redirect()->route('alerts.settings')->with('success', 'Alert deleted successfully.');
    }
}
