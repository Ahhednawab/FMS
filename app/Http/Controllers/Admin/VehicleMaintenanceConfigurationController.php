<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceConfiguration;
use App\Services\VehicleMaintenanceScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleMaintenanceConfigurationController extends Controller
{
    /** Oldest vehicle model year offered on the configuration form. */
    private const EARLIEST_MODEL_YEAR = 1990;

    public function __construct(
        private VehicleMaintenanceScheduleService $vehicleMaintenanceScheduleService
    ) {
        if (!auth()->user()->hasPermission('vehicle_maintenance_configurations')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index(Request $request)
    {
        $query = VehicleMaintenanceConfiguration::where('is_active', 1)
            ->when($request->filled('make'), fn ($q) => $q->where('make', $request->make))
            ->when($request->filled('model'), fn ($q) => $q->where('model', $request->model))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%' . $request->search . '%';
                $q->where(fn ($inner) => $inner->where('make', 'like', $search)
                    ->orWhere('model', 'like', $search));
            });

        // Item filter: find which Make/Models have an interval set for a given
        // predefined item — or are missing one (those generate no alerts).
        $column = $request->filled('item')
            ? VehicleMaintenanceConfiguration::columnForItem($request->item)
            : null;

        if ($column) {
            $request->get('interval_status') === 'missing'
                ? $query->whereNull($column)
                : $query->whereNotNull($column);
        }

        $configurations = $query->orderBy('make')->orderBy('model')->get();

        return view('admin.vehicleMaintenanceConfigurations.index', [
            'configurations' => $configurations,
            'items'          => VehicleMaintenanceConfiguration::ITEMS,
            'makes'          => $this->configuredValues('make'),
            'models'         => $this->configuredValues('model'),
        ]);
    }

    /**
     * Distinct make / model values that actually exist on active
     * configurations, so a filter option can never return an empty list.
     */
    private function configuredValues(string $column)
    {
        return VehicleMaintenanceConfiguration::where('is_active', 1)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->pluck($column)
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function create()
    {
        return view('admin.vehicleMaintenanceConfigurations.create', [
            'configuration' => null,
            'items'         => VehicleMaintenanceConfiguration::ITEMS,
            'makes'         => $this->makeOptions(),
            'modelYears'    => $this->modelYearOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            array_merge($this->makeModelRules(), $this->intervalRules()),
            [
                'model.required' => 'Select at least one Vehicle Model year.',
                'model.array'    => 'Select at least one Vehicle Model year.',
                'model.*.in'     => 'Vehicle Model must be a year between ' . self::EARLIEST_MODEL_YEAR . ' and ' . now()->format('Y') . '.',
            ]
        );

        $make  = trim($validated['make']);
        $years = collect($validated['model'])
            ->map(fn ($year) => (string) $year)
            ->unique()
            ->sort(SORT_NATURAL)
            ->values();

        // Only an active configuration blocks a new one; a previously deleted
        // (is_active = 0) row is revived below instead of colliding with the
        // unique make+model index.
        $clashes = VehicleMaintenanceConfiguration::where('is_active', 1)
            ->where('make', $make)
            ->whereIn('model', $years->all())
            ->orderBy('model')
            ->pluck('model');

        if ($clashes->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'model' => 'A configuration already exists for ' . $make . ' — '
                    . $clashes->implode(', ') . '. Deselect '
                    . ($clashes->count() > 1 ? 'those years' : 'that year')
                    . ' or edit the existing configuration from the list.',
            ]);
        }

        $intervals = array_intersect_key($validated, $this->intervalRules());

        // One configuration row per selected model year: vehicles are matched on
        // an exact make + model pair, so each year needs its own record.
        foreach ($years as $year) {
            $configuration = VehicleMaintenanceConfiguration::updateOrCreate(
                ['make' => $make, 'model' => $year],
                array_merge($intervals, ['is_active' => 1])
            );

            $this->vehicleMaintenanceScheduleService->syncConfiguration($configuration);
        }

        $message = $years->count() === 1
            ? 'Vehicle maintenance configuration created for ' . $make . ' ' . $years->first() . '.'
            : 'Vehicle maintenance configuration created for ' . $make . ' — ' . $years->count()
                . ' model years (' . $years->implode(', ') . ').';

        return redirect()->route('vehicleMaintenanceConfigurations.index')->with('success', $message);
    }

    /**
     * No dedicated detail page — the list already shows every interval.
     */
    public function show(VehicleMaintenanceConfiguration $vehicleMaintenanceConfiguration)
    {
        return redirect()->route('vehicleMaintenanceConfigurations.edit', $vehicleMaintenanceConfiguration->id);
    }

    public function edit(VehicleMaintenanceConfiguration $vehicleMaintenanceConfiguration)
    {
        return view('admin.vehicleMaintenanceConfigurations.edit', [
            'configuration' => $vehicleMaintenanceConfiguration,
            'items'         => VehicleMaintenanceConfiguration::ITEMS,
        ]);
    }

    public function update(Request $request, VehicleMaintenanceConfiguration $vehicleMaintenanceConfiguration)
    {
        // Make + Model are read-only on the Edit page, so only the intervals are
        // validated and updated — they can never be changed from here.
        $validated = $request->validate($this->intervalRules());

        $vehicleMaintenanceConfiguration->update($validated);

        $this->vehicleMaintenanceScheduleService->syncConfiguration($vehicleMaintenanceConfiguration);

        return redirect()->route('vehicleMaintenanceConfigurations.index')
            ->with('success', 'Vehicle maintenance configuration updated successfully.');
    }

    public function destroy(VehicleMaintenanceConfiguration $vehicleMaintenanceConfiguration)
    {
        $vehicleMaintenanceConfiguration->update(['is_active' => 0]);

        return redirect()->route('vehicleMaintenanceConfigurations.index')
            ->with('delete_msg', 'Vehicle maintenance configuration deleted successfully.');
    }

    /**
     * Make + Model rules, used only when creating.
     *
     * Model is a multi-select of vehicle model years, so it arrives as an array
     * and each entry must be one of the offered years. Uniqueness is checked
     * per year in store(), which also revives soft-deleted rows.
     *
     * @return array<string, mixed>
     */
    private function makeModelRules(): array
    {
        return [
            'make'    => ['required', 'string', 'max:255'],
            'model'   => ['required', 'array', 'min:1'],
            'model.*' => ['required', Rule::in($this->modelYearOptions()->all())],
        ];
    }

    /**
     * Selectable vehicle model years: the current year down to the earliest
     * supported year, newest first. The current year is always included.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function modelYearOptions()
    {
        return collect(range((int) now()->format('Y'), self::EARLIEST_MODEL_YEAR))
            ->map(fn (int $year) => (string) $year)
            ->values();
    }

    /**
     * One nullable, non-negative integer rule per predefined maintenance item.
     *
     * @return array<string, array<int, string>>
     */
    private function intervalRules(): array
    {
        $rules = [];

        foreach (VehicleMaintenanceConfiguration::ITEMS as $column) {
            $rules[$column] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    /**
     * Known Vehicle Makes — everything already configured, plus the makes in
     * use on the fleet, so existing vehicles can be onboarded easily.
     */
    private function makeOptions()
    {
        return $this->distinctValues('make');
    }

    private function distinctValues(string $column)
    {
        $fromVehicles = Vehicle::where('is_active', 1)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->pluck($column);

        $fromConfigurations = VehicleMaintenanceConfiguration::whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->pluck($column);

        return $fromVehicles
            ->merge($fromConfigurations)
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
