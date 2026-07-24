<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMileageReport;
use App\Models\InventoryLargerReport;
use App\Models\Product;
use App\Models\ProductList;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenanceConfiguration;
use App\Models\VehicleMaintenancePart;
use App\Models\VehicleMaintenanceWorkDone;
use App\Models\Warehouse;
use App\Models\WarehouseAssignment;
use App\Models\Workshop;
use App\Services\VehicleMaintenanceScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VehicleMaintenanceController extends Controller
{
    public function __construct(
        private VehicleMaintenanceScheduleService $vehicleMaintenanceScheduleService
    ) {}

    public function index(Request $request)
    {
        $vehicleMaintenances = $this->maintenanceQuery($request)
            ->latest('vehicle_maintenances.created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.vehicleMaintenances.index', [
            'vehicleMaintenances' => $vehicleMaintenances,
            'vehicles' => Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id'),
            'vehicleMakes' => Vehicle::where('is_active', 1)->whereNotNull('make')->distinct()->orderBy('make')->pluck('make'),
            'vehicleModels' => Vehicle::where('is_active', 1)->whereNotNull('model')->distinct()->orderBy('model')->pluck('model'),
            'workDones' => VehicleMaintenanceWorkDone::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'warehouses' => Warehouse::subWarehouses()->orderBy('name')->pluck('name', 'id'),
            'workshops' => $this->workshopOptions(),
            'products' => ProductList::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'createdByUsers' => \App\Models\User::orderBy('name')->pluck('name', 'id'),
            'maintenanceTypes' => $this->maintenanceTypes(),
        ]);
    }

    public function create()
    {
        return view('admin.vehicleMaintenances.create', [
            'maintenance_id' => VehicleMaintenance::GetMaintenanceId(),
            'vehicles'       => Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id'),
            'warehouses'     => Warehouse::subWarehouses()->orderBy('name')->pluck('name', 'id'),
            'workshops'      => $this->workshopOptions(),
            'workDones'      => VehicleMaintenanceWorkDone::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'maintenanceTypes' => $this->maintenanceTypes(),
            'predictiveItems' => VehicleMaintenanceConfiguration::itemNames(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMaintenance($request);
        $summary = [];

        DB::transaction(function () use ($validated, $request, &$summary) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $dailyMileage = $this->dailyMileageFor($vehicle->id, $validated['service_date']);
            if (!$dailyMileage) {
                throw ValidationException::withMessages([
                    'odometer_reading' => 'No mileage record found for the selected date.',
                ]);
            }

            $workDones = $this->resolveWorkDones($validated['work_done']);

            $baseMileage = (int) $dailyMileage->current_km;

            $maintenance = VehicleMaintenance::create([
                'maintenance_id'      => $request->maintenance_id ?: VehicleMaintenance::GetMaintenanceId(),
                'vehicle_id'          => $vehicle->id,
                'vehicle_make'        => $vehicle->make,
                'model'               => $vehicle->model,
                'odometer_reading'    => $baseMileage,
                'service_date'        => $validated['service_date'],
                'maintenance_type'    => $validated['maintenance_type'],
                'work_done_id'        => $workDones->first()->id,
                'workshop_id'         => $validated['workshop_id'],
                'labor_cost'          => $validated['labor_cost'] ?? 0,
                'service_cost'        => 0,
                'service_description' => $workDones->pluck('name')->implode(', '),
                'remarks'             => $validated['remarks'] ?? null,
                'created_by'          => auth()->id(),
                'is_active'           => 1,
            ]);

            $maintenance->workDones()->sync($workDones->pluck('id')->all());

            $total = $this->deductParts($maintenance, $validated['parts']);
            $maintenance->update(['service_cost' => $total + (float) ($validated['labor_cost'] ?? 0)]);

            $summary = $this->vehicleMaintenanceScheduleService->recordMaintenance($maintenance);
        });

        return redirect()->route('vehicleMaintenances.index')
            ->with('success', $this->maintenanceSavedMessage('created', $summary));
    }

    public function edit(VehicleMaintenance $vehicleMaintenance)
    {
        $vehicleMaintenance->load(['maintenanceParts.product.unit', 'workDone', 'workDones']);

        return view('admin.vehicleMaintenances.edit', [
            'vehicleMaintenance' => $vehicleMaintenance,
            'maintenance_id'     => $vehicleMaintenance->maintenance_id,
            'vehicles'           => Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id'),
            'warehouses'         => Warehouse::subWarehouses()->orderBy('name')->pluck('name', 'id'),
            'workshops'          => $this->workshopOptions(),
            'workDones'          => VehicleMaintenanceWorkDone::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'maintenanceTypes'   => $this->maintenanceTypes(),
            'predictiveItems'    => VehicleMaintenanceConfiguration::itemNames(),
        ]);
    }

    public function update(Request $request, VehicleMaintenance $vehicleMaintenance)
    {
        $validated = $this->validateMaintenance($request);
        $summary = [];

        DB::transaction(function () use ($validated, $vehicleMaintenance, &$summary) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $dailyMileage = $this->dailyMileageFor($vehicle->id, $validated['service_date']);
            if (!$dailyMileage) {
                throw ValidationException::withMessages([
                    'odometer_reading' => 'No mileage record found for the selected date.',
                ]);
            }

            $workDones = $this->resolveWorkDones($validated['work_done']);

            $baseMileage = (int) $dailyMileage->current_km;

            $this->restoreParts($vehicleMaintenance);

            $vehicleMaintenance->update([
                'vehicle_id'          => $vehicle->id,
                'vehicle_make'        => $vehicle->make,
                'model'               => $vehicle->model,
                'odometer_reading'    => $baseMileage,
                'service_date'        => $validated['service_date'],
                'maintenance_type'    => $validated['maintenance_type'],
                'work_done_id'        => $workDones->first()->id,
                'workshop_id'         => $validated['workshop_id'],
                'labor_cost'          => $validated['labor_cost'] ?? 0,
                'service_description' => $workDones->pluck('name')->implode(', '),
                'remarks'             => $validated['remarks'] ?? null,
            ]);

            $vehicleMaintenance->workDones()->sync($workDones->pluck('id')->all());

            $total = $this->deductParts($vehicleMaintenance, $validated['parts']);
            $vehicleMaintenance->update(['service_cost' => $total + (float) ($validated['labor_cost'] ?? 0)]);

            $summary = $this->vehicleMaintenanceScheduleService->recordMaintenance($vehicleMaintenance);
        });

        return redirect()->route('vehicleMaintenances.index')
            ->with('success', $this->maintenanceSavedMessage('updated', $summary));
    }

    public function show(VehicleMaintenance $vehicleMaintenance)
    {
        $vehicleMaintenance->load(['vehicle', 'workDone', 'workDones', 'warehouse', 'workshop', 'maintenanceParts.product.unit', 'maintenanceParts.warehouse', 'createdBy']);

        return view('admin.vehicleMaintenances.show', compact('vehicleMaintenance'));
    }

    public function destroy(VehicleMaintenance $vehicleMaintenance)
    {
        DB::transaction(function () use ($vehicleMaintenance) {
            $this->restoreParts($vehicleMaintenance);
            $vehicleMaintenance->update(['is_active' => 0]);
        });

        return redirect()->route('vehicleMaintenances.index')->with('delete_msg', 'Vehicle maintenance deleted successfully.');
    }

    /**
     * Remove a Work Done option from the dropdown (soft delete so
     * existing maintenance records keep their reference).
     */
    public function destroyWorkDone(VehicleMaintenanceWorkDone $workDone)
    {
        $workDone->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Find or create each Work Done option by name, reactivating any
     * that were previously deleted from the dropdown.
     */
    private function resolveWorkDones(array $names): \Illuminate\Support\Collection
    {
        return collect($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->map(function (string $name) {
                $workDone = VehicleMaintenanceWorkDone::firstOrCreate(
                    ['name' => $name],
                    ['is_active' => true]
                );

                if (! $workDone->is_active) {
                    $workDone->update(['is_active' => true]);
                }

                return $workDone;
            })
            ->values();
    }

    public function vehicleDetails(Vehicle $vehicle)
    {
        // Only sub warehouses can be picked on a maintenance row, so never
        // suggest the master warehouse as a row default.
        $warehouse = Warehouse::subWarehouses()
            ->where('station_id', $vehicle->station_id)
            ->first();

        return response()->json([
            'make'         => $vehicle->make,
            'model'        => $vehicle->model,
            'warehouse_id' => $warehouse?->id,
        ]);
    }

    /**
     * Return the configured predictive maintenance intervals (KM) for a
     * vehicle's make + model, used by the form to preview the next due mileage.
     */
    public function configIntervals(Vehicle $vehicle)
    {
        $config = VehicleMaintenanceConfiguration::forVehicle($vehicle->make, $vehicle->model);

        $items = [];

        if ($config) {
            foreach (VehicleMaintenanceConfiguration::itemNames() as $item) {
                $interval = $config->intervalFor($item);
                if ($interval !== null) {
                    $items[$item] = $interval;
                }
            }
        }

        return response()->json([
            'has_config' => (bool) $config,
            'items'      => $items,
        ]);
    }

    /**
     * Live predictive maintenance alerts (Upcoming + Due) for the dashboard.
     * Paginated to match the dashboard's generic notification renderer.
     */
    public function predictiveAlerts(Request $request)
    {
        $alerts = $this->vehicleMaintenanceScheduleService->dashboardAlerts([
            'vehicle_id' => $request->get('vehicle_id'),
            'title'      => $request->get('title'),
        ]);

        $perPage = 10;
        $page = max(1, (int) $request->get('page', 1));
        $items = $alerts->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $alerts->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json(['data' => $paginator]);
    }

    /**
     * Distinct alert titles for the dashboard "Alert" filter dropdown.
     */
    public function predictiveAlertTitles()
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->vehicleMaintenanceScheduleService->alertFilterTitles(),
        ]);
    }

    public function dailyMileage(Vehicle $vehicle, Request $request)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
        ]);

        $dailyMileage = $this->dailyMileageFor($vehicle->id, $validated['service_date']);

        if (!$dailyMileage) {
            return response()->json([
                'success' => false,
                'message' => 'No mileage record found for the selected date.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'mileage' => $dailyMileage->current_km,
        ]);
    }

    public function warehouseProducts(Warehouse $warehouse)
    {
        $products = WarehouseAssignment::query()
            ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
            ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
            ->leftJoin('units', 'pl.unit_id', '=', 'units.id')
            ->where('warehouse_assignments.warehouse_id', $warehouse->id)
            ->where('warehouse_assignments.quantity', '>', 0)
            ->groupBy('mwi.product_id', 'pl.name', 'units.name')
            ->orderBy('pl.name')
            ->get([
                'mwi.product_id as id',
                'pl.name',
                DB::raw('units.name as unit_name'),
                DB::raw('SUM(warehouse_assignments.quantity) as available_quantity'),
                DB::raw('MAX(warehouse_assignments.price) as unit_price'),
            ]);

        return response()->json($products);
    }

    private function validateMaintenance(Request $request): array
    {
        return $request->validate([
            'vehicle_id'       => 'required|exists:vehicles,id',
            'service_date'     => 'required|date',
            'maintenance_type' => 'required|in:' . implode(',', array_keys($this->maintenanceTypes())),
            'work_done'        => 'required|array|min:1',
            'work_done.*'      => 'required|string|max:255',
            'workshop_id'      => 'required|exists:workshops,id',
            'labor_cost'       => 'nullable|numeric|min:0',
            'remarks'          => 'nullable|string',
            'parts'            => 'required|array|min:1',
            // Only active sub warehouses may be used — never the master warehouse.
            'parts.*.warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where(
                    fn ($query) => $query->where('is_master', false)->where('is_active', 1)
                ),
            ],
            'parts.*.product_id'   => 'required|exists:products_list,id',
            'parts.*.quantity'     => 'required|numeric|min:0.01',
            'parts.*.unit_price'   => 'required|numeric|min:0',
        ], [
            'parts.*.warehouse_id.required' => 'Select a warehouse for each product row.',
            'parts.*.warehouse_id.exists'   => 'Select a valid sub warehouse for each product row.',
            'parts.*.product_id.required'   => 'Select a product for each product row.',
        ]);
    }

    /**
     * Deduct each part from the warehouse selected on its own row, so a single
     * maintenance record can draw products from several warehouses.
     */
    private function deductParts(VehicleMaintenance $maintenance, array $parts): float
    {
        $total = 0;

        // Stock is checked per warehouse + product pair, so the same product
        // requested twice from one warehouse is validated against the combined
        // quantity rather than each row in isolation.
        $requested = collect($parts)
            ->groupBy(fn ($part) => $part['warehouse_id'] . '|' . $part['product_id'])
            ->map(fn ($rows) => [
                'warehouse_id' => (int) $rows->first()['warehouse_id'],
                'product_id'   => (int) $rows->first()['product_id'],
                'quantity'     => (float) $rows->sum('quantity'),
            ]);

        foreach ($requested as $row) {
            $available = (float) WarehouseAssignment::query()
                ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
                ->where('warehouse_assignments.warehouse_id', $row['warehouse_id'])
                ->where('mwi.product_id', $row['product_id'])
                ->sum('warehouse_assignments.quantity');

            if ($row['quantity'] > $available) {
                $product = ProductList::with('unit')->find($row['product_id']);
                $productName = $product?->name ?? 'selected product';
                $unitSuffix = $product?->unit?->name ? ' ' . $product->unit->name : '';
                $warehouseName = Warehouse::find($row['warehouse_id'])?->name ?? 'selected warehouse';
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parts' => "Insufficient stock for {$productName} in {$warehouseName}. Available: {$available}{$unitSuffix}, requested: {$row['quantity']}{$unitSuffix}.",
                ]);
            }
        }

        foreach ($parts as $part) {
            $remaining = (float) $part['quantity'];
            $warehouseId = (int) $part['warehouse_id'];

            $assignments = WarehouseAssignment::query()
                ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
                ->where('warehouse_assignments.warehouse_id', $warehouseId)
                ->where('mwi.product_id', $part['product_id'])
                ->where('warehouse_assignments.quantity', '>', 0)
                ->orderBy('warehouse_assignments.expiry_date')
                ->select('warehouse_assignments.*')
                ->lockForUpdate()
                ->get();

            foreach ($assignments as $assignment) {
                if ($remaining <= 0) {
                    break;
                }

                $used = min($remaining, (float) $assignment->quantity);
                $unitPrice = (float) $assignment->price;
                $lineTotal = $used * $unitPrice;

                $assignment->decrement('quantity', $used);

                VehicleMaintenancePart::create([
                    'vehicle_maintenance_id' => $maintenance->id,
                    'warehouse_assignment_id' => $assignment->id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $part['product_id'],
                    'quantity' => $used,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);

                $this->recordInventoryMovement($maintenance, $part['product_id'], $warehouseId, $used, $unitPrice);
                $total += $lineTotal;
                $remaining -= $used;
            }
        }

        return $total;
    }

    private function restoreParts(VehicleMaintenance $maintenance): void
    {
        $maintenance->loadMissing('maintenanceParts');

        foreach ($maintenance->maintenanceParts as $part) {
            if ($part->warehouse_assignment_id) {
                WarehouseAssignment::where('id', $part->warehouse_assignment_id)->increment('quantity', $part->quantity);
            }
            $part->delete();
        }
    }

    private function recordInventoryMovement(VehicleMaintenance $maintenance, int $productId, int $warehouseId, int $quantity, float $unitPrice): void
    {
        if (!Schema::hasTable('inventory_larger_reports')) {
            return;
        }

        $report = new InventoryLargerReport;
        $report->report_id = InventoryLargerReport::GetReportId();
        $report->report_date = $maintenance->service_date;
        $product = ProductList::find($productId);
        $report->product_id = $this->inventoryReportProductId($productId, $warehouseId);
        if (Schema::hasColumn('inventory_larger_reports', 'product_name')) {
            $report->product_name = $product?->name ?? 'Maintenance Part';
        }
        $report->warehouse_id = $warehouseId;
        $report->category = $this->lookupId('inventory_larger_report_category', 'Vehicle Maintenance');
        $report->location = 'Vehicle Maintenance';
        $report->transaction_type = $this->lookupId('transaction_types', 'Maintenance Issue');
        $report->supplier_id = $this->lookupId('suppliers', 'Own');
        $report->order_quantity = $quantity;
        $report->order_price = $unitPrice;
        $report->status = $this->lookupId('inventory_larger_report_status', 'Completed');
        $report->delievery_date = $maintenance->service_date;

        $report->save();
    }

    private function inventoryReportProductId(int $productListId, int $warehouseId): int
    {
        $productId = Product::where('product_id', $productListId)
            ->where('warehouse_id', $warehouseId)
            ->value('id');

        if ($productId) {
            return (int) $productId;
        }

        return (int) (Product::where('product_id', $productListId)->value('id') ?: $productListId);
    }

    private function lookupId(string $table, string $name): int
    {
        $id = DB::table($table)->where('name', $name)->value('id');

        if ($id) {
            return (int) $id;
        }

        return (int) DB::table($table)->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function maintenanceQuery(Request $request)
    {
        return VehicleMaintenance::query()
            ->with(['vehicle', 'workDone', 'workDones', 'warehouse', 'workshop', 'maintenanceParts.product.unit', 'maintenanceParts.warehouse', 'createdBy'])
            ->where('vehicle_maintenances.is_active', 1)
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('service_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('service_date', '<=', $request->to_date))
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->vehicle_id))
            ->when($request->filled('vehicle_make'), fn ($query) => $query->where('vehicle_make', $request->vehicle_make))
            ->when($request->filled('vehicle_model'), fn ($query) => $query->where('model', $request->vehicle_model))
            ->when($request->filled('maintenance_type'), fn ($query) => $query->where('maintenance_type', $request->maintenance_type))
            ->when($request->filled('work_done_id'), fn ($query) => $query->where(function ($inner) use ($request) {
                $inner->where('work_done_id', $request->work_done_id)
                    ->orWhereHas('workDones', fn ($workDones) => $workDones->where('vehicle_maintenance_work_dones.id', $request->work_done_id));
            }))
            // Warehouse now lives on each product row, so match any maintenance
            // record that drew a part from the selected warehouse.
            ->when($request->filled('warehouse_id'), fn ($query) => $query->whereHas(
                'maintenanceParts',
                fn ($parts) => $parts->where('vehicle_maintenance_parts.warehouse_id', $request->warehouse_id)
            ))
            ->when($request->filled('workshop_id'), fn ($query) => $query->where('workshop_id', $request->workshop_id))
            ->when($request->filled('created_by'), fn ($query) => $query->where('created_by', $request->created_by))
            ->when($request->filled('amount_min'), fn ($query) => $query->where('service_cost', '>=', $request->amount_min))
            ->when($request->filled('amount_max'), fn ($query) => $query->where('service_cost', '<=', $request->amount_max))
            ->when($request->filled('product_id'), fn ($query) => $query->whereHas('maintenanceParts', fn ($parts) => $parts->where('product_id', $request->product_id)))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('maintenance_id', 'like', $search)
                        ->orWhere('vehicle_make', 'like', $search)
                        ->orWhere('model', 'like', $search)
                        ->orWhereHas('vehicle', fn ($vehicle) => $vehicle->where('vehicle_no', 'like', $search))
                        ->orWhereHas('workDone', fn ($workDone) => $workDone->where('name', 'like', $search))
                        ->orWhereHas('workDones', fn ($workDones) => $workDones->where('name', 'like', $search));
                });
            });
    }

    private function maintenanceTypes(): array
    {
        return [
            'predictive' => 'Predictive Maintenance',
            'preventive' => 'Preventive Maintenance',
            'corrective' => 'Corrective Maintenance',
        ];
    }

    /**
     * Build the flash message shown after a maintenance record is saved,
     * including the automatically-calculated next predictive due mileages.
     *
     * @param  array<int, array{item: string, interval: int, next_due: int}>  $summary
     */
    private function maintenanceSavedMessage(string $action, array $summary): string
    {
        $message = "Vehicle maintenance {$action} successfully.";

        if (!empty($summary)) {
            $parts = array_map(
                fn ($row) => "{$row['item']} at " . number_format($row['next_due']) . ' KM',
                $summary
            );

            $message .= ' Next predictive maintenance will be due — ' . implode('; ', $parts) . '.';
        }

        return $message;
    }

    private function workshopOptions()
    {
        if (Schema::hasTable('service_providers') && Workshop::count() === 0) {
            DB::table('service_providers')
                ->whereNotNull('name')
                ->orderBy('name')
                ->pluck('name')
                ->each(fn ($name) => Workshop::firstOrCreate(['name' => $name], ['is_active' => true]));
        }

        return Workshop::where('is_active', 1)->orderBy('name')->pluck('name', 'id');
    }

    private function dailyMileageFor(int $vehicleId, string $serviceDate): ?DailyMileageReport
    {
        return DailyMileageReport::query()
            ->where('vehicle_id', $vehicleId)
            ->where('is_active', 1)
            ->whereDate('report_date', Carbon::parse($serviceDate)->toDateString())
            ->orderByDesc('id')
            ->first();
    }
}

