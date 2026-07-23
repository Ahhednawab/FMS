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

    public function index()
    {
        $configurations = VehicleMaintenanceConfiguration::where('is_active', 1)
            ->orderBy('make')
            ->orderBy('model')
            ->get();

        return view('admin.vehicleMaintenanceConfigurations.index', [
            'configurations' => $configurations,
            'items'          => VehicleMaintenanceConfiguration::ITEMS,
        ]);
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
        $validated = $this->validateConfiguration($request);

        $configuration = VehicleMaintenanceConfiguration::create(
            array_merge($validated, ['is_active' => 1])
        );

        $this->vehicleMaintenanceScheduleService->syncConfiguration($configuration);

        return redirect()->route('vehicleMaintenanceConfigurations.index')
            ->with('success', 'Vehicle maintenance configuration created successfully.');
    }

    public function edit(VehicleMaintenanceConfiguration $vehicleMaintenanceConfiguration)
    {
        return view('admin.vehicleMaintenanceConfigurations.edit', [
            'configuration' => $vehicleMaintenanceConfiguration,
            'items'         => VehicleMaintenanceConfiguration::ITEMS,
            'makes'         => $this->makeOptions(),
            'models'        => $this->modelOptions(),
        ]);
    }

    public function update(Request $request, VehicleMaintenanceConfiguration $vehicleMaintenanceConfiguration)
    {
        $validated = $this->validateConfiguration($request, $vehicleMaintenanceConfiguration->id);

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
     * Validate make + model (unique together) and every predefined interval.
     */
    private function validateConfiguration(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'make'  => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicle_maintenance_configurations')
                    ->where(fn ($query) => $query->where('model', $request->input('model')))
                    ->ignore($ignoreId),
            ],
            'model' => ['required', 'string', 'max:255'],
        ];

        foreach (VehicleMaintenanceConfiguration::ITEMS as $column) {
            $rules[$column] = ['nullable', 'integer', 'min:0'];
        }

        return $request->validate($rules, [
            'make.unique' => 'A configuration for this Make and Model already exists.',
        ]);
    }

    private function makeOptions()
    {
        return Vehicle::where('is_active', 1)
            ->whereNotNull('make')
            ->where('make', '!=', '')
            ->distinct()
            ->orderBy('make')
            ->pluck('make');
    }

    private function modelOptions()
    {
        return Vehicle::where('is_active', 1)
            ->whereNotNull('model')
            ->where('model', '!=', '')
            ->distinct()
            ->orderBy('model')
            ->pluck('model');
    }
}
