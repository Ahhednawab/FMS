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
            'models'        => $this->modelOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            array_merge($this->makeModelRules($request), $this->intervalRules()),
            ['make.unique' => 'A configuration for this Make and Model already exists.']
        );

        // Keyed on make + model so a previously deleted (is_active = 0) row is
        // revived rather than colliding with the unique index.
        $configuration = VehicleMaintenanceConfiguration::updateOrCreate(
            [
                'make'  => $validated['make'],
                'model' => $validated['model'],
            ],
            array_merge(
                array_intersect_key($validated, $this->intervalRules()),
                ['is_active' => 1]
            )
        );

        $this->vehicleMaintenanceScheduleService->syncConfiguration($configuration);

        return redirect()->route('vehicleMaintenanceConfigurations.index')
            ->with('success', 'Vehicle maintenance configuration created successfully.');
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
     * Make + Model rules, used only when creating. The pair must be unique
     * among active configurations (a soft-deleted pair is revived instead).
     *
     * @return array<string, mixed>
     */
    private function makeModelRules(Request $request): array
    {
        return [
            'make'  => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_maintenance_configurations')->where(
                    fn ($query) => $query
                        ->where('model', $request->input('model'))
                        ->where('is_active', 1)
                ),
            ],
            'model' => ['required', 'string', 'max:255'],
        ];
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

    private function modelOptions()
    {
        return $this->distinctValues('model');
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
