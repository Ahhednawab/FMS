<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMileageReport;
use App\Models\InventoryLargerReport;
use App\Models\Product;
use App\Models\ProductList;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMaintenancePart;
use App\Models\VehicleMaintenanceWorkDone;
use App\Models\Warehouse;
use App\Models\WarehouseAssignment;
use App\Models\Workshop;
use App\Services\VehicleMaintenanceScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            'warehouses' => Warehouse::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
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
            'vehicles' => Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id'),
            'warehouses' => Warehouse::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'workshops' => $this->workshopOptions(),
            'workDones' => VehicleMaintenanceWorkDone::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'maintenanceTypes' => $this->maintenanceTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMaintenance($request);

        DB::transaction(function () use ($validated, $request) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $dailyMileage = $this->dailyMileageFor($vehicle->id, $validated['service_date']);
            if (!$dailyMileage) {
                throw ValidationException::withMessages([
                    'odometer_reading' => 'No mileage record found for the selected date.',
                ]);
            }

            $workDone = VehicleMaintenanceWorkDone::firstOrCreate([
                'name' => trim($validated['work_done']),
            ], [
                'is_active' => true,
            ]);

            $maintenance = VehicleMaintenance::create([
                'maintenance_id' => $request->maintenance_id ?: VehicleMaintenance::GetMaintenanceId(),
                'vehicle_id' => $vehicle->id,
                'vehicle_make' => $vehicle->make,
                'model' => $vehicle->model,
                'odometer_reading' => (int) $dailyMileage->mileage,
                'service_date' => $validated['service_date'],
                'maintenance_type' => $validated['maintenance_type'],
                'work_done_id' => $workDone->id,
                'warehouse_id' => $validated['warehouse_id'],
                'workshop_id' => $validated['workshop_id'],
                'labor_cost' => $validated['labor_cost'] ?? 0,
                'service_cost' => 0,
                'service_description' => $workDone->name,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            $total = $this->deductParts($maintenance, $validated['warehouse_id'], $validated['parts']);
            $maintenance->update(['service_cost' => $total + (float) ($validated['labor_cost'] ?? 0)]);

            $this->vehicleMaintenanceScheduleService->recordMaintenance($maintenance);
        });

        return redirect()->route('vehicleMaintenances.index')->with('success', 'Vehicle maintenance created successfully.');
    }

    public function edit(VehicleMaintenance $vehicleMaintenance)
    {
        $vehicleMaintenance->load(['maintenanceParts.product', 'workDone']);

        return view('admin.vehicleMaintenances.edit', [
            'vehicleMaintenance' => $vehicleMaintenance,
            'maintenance_id' => $vehicleMaintenance->maintenance_id,
            'vehicles' => Vehicle::where('is_active', 1)->orderBy('vehicle_no')->pluck('vehicle_no', 'id'),
            'warehouses' => Warehouse::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'workshops' => $this->workshopOptions(),
            'workDones' => VehicleMaintenanceWorkDone::where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'maintenanceTypes' => $this->maintenanceTypes(),
        ]);
    }

    public function update(Request $request, VehicleMaintenance $vehicleMaintenance)
    {
        $validated = $this->validateMaintenance($request);

        DB::transaction(function () use ($validated, $vehicleMaintenance) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $dailyMileage = $this->dailyMileageFor($vehicle->id, $validated['service_date']);
            if (!$dailyMileage) {
                throw ValidationException::withMessages([
                    'odometer_reading' => 'No mileage record found for the selected date.',
                ]);
            }

            $workDone = VehicleMaintenanceWorkDone::firstOrCreate([
                'name' => trim($validated['work_done']),
            ], [
                'is_active' => true,
            ]);

            $this->restoreParts($vehicleMaintenance);

            $vehicleMaintenance->update([
                'vehicle_id' => $vehicle->id,
                'vehicle_make' => $vehicle->make,
                'model' => $vehicle->model,
                'odometer_reading' => (int) $dailyMileage->mileage,
                'service_date' => $validated['service_date'],
                'maintenance_type' => $validated['maintenance_type'],
                'work_done_id' => $workDone->id,
                'warehouse_id' => $validated['warehouse_id'],
                'workshop_id' => $validated['workshop_id'],
                'labor_cost' => $validated['labor_cost'] ?? 0,
                'service_description' => $workDone->name,
            ]);

            $total = $this->deductParts($vehicleMaintenance, $validated['warehouse_id'], $validated['parts']);
            $vehicleMaintenance->update(['service_cost' => $total + (float) ($validated['labor_cost'] ?? 0)]);

            $this->vehicleMaintenanceScheduleService->recordMaintenance($vehicleMaintenance);
        });

        return redirect()->route('vehicleMaintenances.index')->with('success', 'Vehicle maintenance updated successfully.');
    }

    public function show(VehicleMaintenance $vehicleMaintenance)
    {
        $vehicleMaintenance->load(['vehicle', 'workDone', 'warehouse', 'workshop', 'maintenanceParts.product', 'createdBy']);

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

    public function vehicleDetails(Vehicle $vehicle)
    {
        $warehouse = Warehouse::where('is_active', 1)
            ->where('station_id', $vehicle->station_id)
            ->first();

        return response()->json([
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'warehouse_id' => $warehouse?->id,
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
            'mileage' => $dailyMileage->mileage,
        ]);
    }

    public function warehouseProducts(Warehouse $warehouse)
    {
        $products = WarehouseAssignment::query()
            ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
            ->join('products_list as pl', 'mwi.product_id', '=', 'pl.id')
            ->where('warehouse_assignments.warehouse_id', $warehouse->id)
            ->where('warehouse_assignments.quantity', '>', 0)
            ->groupBy('mwi.product_id', 'pl.name')
            ->orderBy('pl.name')
            ->get([
                'mwi.product_id as id',
                'pl.name',
                DB::raw('SUM(warehouse_assignments.quantity) as available_quantity'),
                DB::raw('MAX(warehouse_assignments.price) as unit_price'),
            ]);

        return response()->json($products);
    }

    private function validateMaintenance(Request $request): array
    {
        return $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_date' => 'required|date',
            'maintenance_type' => 'required|in:' . implode(',', array_keys($this->maintenanceTypes())),
            'work_done' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'workshop_id' => 'required|exists:workshops,id',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts' => 'required|array|min:1',
            'parts.*.product_id' => 'required|exists:products_list,id',
            'parts.*.quantity' => 'required|integer|min:1',
            'parts.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    private function deductParts(VehicleMaintenance $maintenance, int $warehouseId, array $parts): float
    {
        $total = 0;
        $requestedByProduct = collect($parts)
            ->groupBy('product_id')
            ->map(fn ($rows) => (int) $rows->sum('quantity'));

        foreach ($requestedByProduct as $productId => $quantity) {
            $available = WarehouseAssignment::query()
                ->join('master_warehouse_inventory as mwi', 'warehouse_assignments.master_inventory_id', '=', 'mwi.id')
                ->where('warehouse_assignments.warehouse_id', $warehouseId)
                ->where('mwi.product_id', $productId)
                ->sum('warehouse_assignments.quantity');

            if ($quantity > $available) {
                $productName = ProductList::find($productId)?->name ?? 'selected product';
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parts' => "Insufficient stock for {$productName}. Available: {$available}, requested: {$quantity}.",
                ]);
            }
        }

        foreach ($parts as $part) {
            $remaining = (int) $part['quantity'];

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

                $used = min($remaining, (int) $assignment->quantity);
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
            ->with(['vehicle', 'workDone', 'warehouse', 'workshop', 'maintenanceParts.product', 'createdBy'])
            ->where('vehicle_maintenances.is_active', 1)
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('service_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('service_date', '<=', $request->to_date))
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->vehicle_id))
            ->when($request->filled('vehicle_make'), fn ($query) => $query->where('vehicle_make', $request->vehicle_make))
            ->when($request->filled('vehicle_model'), fn ($query) => $query->where('model', $request->vehicle_model))
            ->when($request->filled('maintenance_type'), fn ($query) => $query->where('maintenance_type', $request->maintenance_type))
            ->when($request->filled('work_done_id'), fn ($query) => $query->where('work_done_id', $request->work_done_id))
            ->when($request->filled('warehouse_id'), fn ($query) => $query->where('warehouse_id', $request->warehouse_id))
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
                        ->orWhereHas('workDone', fn ($workDone) => $workDone->where('name', 'like', $search));
                });
            });
    }

    private function maintenanceTypes(): array
    {
        return [
            'preventive' => 'Preventive Maintenance',
            'corrective' => 'Corrective Maintenance',
            'emergency' => 'Emergency Maintenance',
        ];
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
