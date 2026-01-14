<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobCartController;
use App\Http\Controllers\Admin\IbcController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\DailyFuelController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\EmployeeAdvanceController;
use App\Http\Controllers\MasterwarehouseController;
use App\Http\Controllers\Admin\MaintainerController;
use App\Http\Controllers\Admin\WarehousesController;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\InventoryRequestController;
use App\Http\Controllers\Admin\BankPaymentController;
use App\Http\Controllers\Admin\CashPaymentController;
use App\Http\Controllers\Admin\LedderMakerController;
use App\Http\Controllers\Admin\ProductListController;
use App\Http\Controllers\Admin\DailyMileageController;
use App\Http\Controllers\Admin\ClientInvoiceController;
use App\Http\Controllers\Admin\AccidentDetailController;
use App\Http\Controllers\Admin\AccidentReportController;
use App\Http\Controllers\Admin\TrackerMileageController;
use App\Http\Controllers\Admin\DailyFuelReportController;
use App\Http\Controllers\Admin\InventoryDemandController;
use App\Http\Controllers\Admin\DailyFuelMileageController;
use App\Http\Controllers\Admin\InsuranceCompanyController;
use App\Http\Controllers\Admin\DriversAttendanceController;
use App\Http\Controllers\Admin\InventoryDispatchController;
use App\Http\Controllers\Admin\DailyMileageReportController;
use App\Http\Controllers\Admin\InventoryWarehouseController;
use App\Http\Controllers\Admin\VehicleMaintenanceController;
use App\Http\Controllers\Admin\VehiclesAttendanceController;
use App\Http\Controllers\Admin\InventoryLargerReportController;
use App\Http\Controllers\Admin\MasterWarehouseInventoryController;
use App\Http\Controllers\Admin\VehicleMaintenanceReportController;

Route::get('/', function () {

    if (Auth::check()) {
        return redirect()->route('admin.index');
    }
    return redirect()->route('login');
});


Route::get('/run-migration/{name}', function ($name) {
    // Execute the migration command
    $output = Artisan::call("migrate --path=database/migrations/$name.php");

    return response()->json([
        'message' => "Migration $name executed successfully!",
        'output' => Artisan::output()
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        if (Auth::check()) {
            return redirect()->route('admin.index');
        }
        return app(CustomLoginController::class)->showLoginForm();
    })->name('login');

    Route::post('/login', [CustomLoginController::class, 'login']);
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'can:access-admin-dashboard'])
    ->name('admin.index');

Route::get('users/getmanagers', [UserController::class, 'getManagers'])
    ->name('users.getmanagers');

Route::middleware(['auth'])->group(function () {


    // Drafts Management
    Route::get('/drafts', [App\Http\Controllers\Admin\DraftController::class, 'index'])->name('drafts.index');
    Route::get('/drafts/{draft}/edit', [App\Http\Controllers\Admin\DraftController::class, 'edit'])->name('drafts.edit');
    Route::delete('/drafts/{draft}', [App\Http\Controllers\Admin\DraftController::class, 'destroy'])->name('drafts.destroy');
    Route::get('/drafts/download/{path}', [App\Http\Controllers\Admin\DraftController::class, 'downloadFile'])->name('drafts.download');
    Route::get('/drafts/view/{path}', [App\Http\Controllers\Admin\DraftController::class, 'viewFile'])->name('drafts.view');
    Route::post('/drafts/{draft}/remove-file', [App\Http\Controllers\Admin\DraftController::class, 'removeFile'])->name('drafts.removeFile');


    Route::resource('users', UserController::class);
    Route::resource('cities', CityController::class);
    Route::resource('stations', StationController::class);
    Route::resource('ibcCenters', IbcController::class);





    Route::resource('vehicles', VehicleController::class);
    Route::post('vehicles/destroyMultiple', [VehicleController::class, 'destroyMultiple'])->name('vehicles.destroyMultiple');
    Route::resource('drivers', DriverController::class);
    Route::post('drivers/destroyMultiple', [DriverController::class, 'destroyMultiple'])->name('drivers.destroyMultiple');
    Route::resource('vendors', VendorController::class);
    Route::resource('insurance-companies', InsuranceCompanyController::class);

    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);
    // Fleet Transactions
    Route::post('dailyMileages/destroyMultiple', [DailyMileageController::class, 'destroyMultiple'])->name('dailyMileages.destroyMultiple');
    Route::resource('dailyMileages', DailyMileageController::class);
    Route::get('fetchDailyMilages', [DailyMileageController::class, 'fetchDailyMilages'])->name('fetchDailyMilages');

    Route::resource('dailyMileageReports', DailyMileageReportController::class);
    Route::resource('dailyFuels', DailyFuelController::class);
    Route::resource('dailyFuelReports', DailyFuelReportController::class);

    Route::resource('productList', ProductListController::class);


    Route::get('/warehouses/assign', [WarehousesController::class, 'assignWarehouse'])->name('warehouses.assign');
    Route::post('/warehouses/create', [WarehousesController::class, 'createWarehouse'])->name('warehouses.create');
    Route::post('/warehouses/request-inventory', [WarehousesController::class, 'requestInventory'])->name('warehouses.request_inventory');
    Route::post('/warehouses/issue-inventory', [WarehousesController::class, 'issueInventory'])->name('warehouses.issue_inventory');
    Route::resource('warehouses', WarehouseController::class);

    Route::get('/assigned-inventory', [MasterWarehouseInventoryController::class, 'assigned'])
        ->name('assigned_inventory.index');


    Route::get('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'index'])->name('master_warehouse_inventory.index');
    Route::get('/master-warehouse-inventory/create', [MasterWarehouseInventoryController::class, 'create'])->name('master_warehouse_inventory.create');
    Route::post('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'store'])->name('master_warehouse_inventory.store');
    Route::post('/master-inventory/assign', [MasterWarehouseInventoryController::class, 'assignStock'])
        ->name('master_warehouse_inventory.assign');

    // Route::get(
    //     'inventory-requests',
    //     [InventoryRequestController::class, 'index']
    // )->name('inventory-requests.index');


    Route::resource(
        'inventory-requests',
        InventoryRequestController::class
    )->only(['index', 'store']);

    Route::post('inventory-requests/{inventoryRequest}/approve', [InventoryRequestController::class, 'approve'])
        ->name('inventory-requests.approve');

    Route::post('inventory-requests/{inventoryRequest}/reject', [InventoryRequestController::class, 'reject'])
        ->name('inventory-requests.reject');



    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::resource('suppliers', SupplierController::class);
    Route::resource('issues', IssueController::class);


    // Accidents
    Route::resource('accidentDetails', AccidentDetailController::class);
    Route::get('accidentDetails/get-vehicle-info/{vehicleNo}', [AccidentDetailController::class, 'getVehicleInfo'])->name('accidentDetails.getVehicleInfo');
    Route::delete('accidentDetails/delete-file/{fileId}', [AccidentDetailController::class, 'deleteFile'])->name('accidentDetails.deleteFile');
    Route::resource('accidentReports', AccidentReportController::class);

    Route::resource('clientInvoices', ClientInvoiceController::class);
    Route::resource('cashPayments', CashPaymentController::class);
    Route::resource('bankPayments', BankPaymentController::class);


    // Vehicle Maintenance
    Route::resource('vehicleMaintenances', VehicleMaintenanceController::class);
    Route::resource('vehicleMaintenanceReports', VehicleMaintenanceReportController::class);


    // Attendance
    // Filter (POST) route to reuse create() for filtering
    Route::post('driverAttendances/create', [DriversAttendanceController::class, 'create'])->name('driverAttendances.filter');
    Route::post('driverAttendances/destroyMultiple', [DriversAttendanceController::class, 'destroyMultiple'])->name('driverAttendances.destroyMultiple');
    Route::resource('driverAttendances', DriversAttendanceController::class);

    Route::post('vehicleAttendances/create', [VehiclesAttendanceController::class, 'create'])->name('vehicleAttendances.filter');
    Route::post('vehicleAttendances/destroyMultiple', [VehiclesAttendanceController::class, 'destroyMultiple'])->name('vehicleAttendances.destroyMultiple');
    Route::resource('vehicleAttendances', VehiclesAttendanceController::class);



    Route::get('/salaries/by-month/{month}', [SalaryController::class, 'getByMonth'])
        ->name('salaries.byMonth');
    Route::resource('salaries', SalaryController::class);


    //   Route::post('salaries/store', [SalaryController::class, 'store'])
    //     ->name('salaries.store');

    // 🔥 AJAX per-row save
    Route::post('salaries/save-single', [SalaryController::class, 'saveSingle'])
        ->name('salaries.save-single');

    Route::resource('employee-advances', EmployeeAdvanceController::class);



    Route::get('advances/', [EmployeeAdvanceController::class, 'index'])->name('advance.index');
    Route::get('advances/create', [EmployeeAdvanceController::class, 'create'])->name('advance.create');
    Route::post('advances/store', [EmployeeAdvanceController::class, 'store'])->name('advance.store');


    Route::resource('invoices', InvoiceController::class);
});

Route::prefix('admin')->name('admin.')->middleware('auth', 'role:admin')->group(function () {

    //General  
    // Route::get('users/getmanagers', [UserController::class, 'getManagers'])
    //     ->name('users.getmanagers');


    // Route::resource('user-permissions', UserPermissionController::class)->only(['edit', 'update']);
    Route::get('role-permissions/{role}/edit', [RolePermissionController::class, 'edit'])
        ->name('role-permissions.edit');

    Route::put('role-permissions/{role}', [RolePermissionController::class, 'update'])
        ->name('role-permissions.update');

    Route::resource('roles', RoleController::class);
    Route::resource('cities', CityController::class);
    Route::resource('stations', StationController::class);
    Route::resource('ibcCenters', IbcController::class);
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('issues', IssueController::class);



    // Master Data
    Route::resource('vehicles', VehicleController::class);
    Route::post('vehicles/destroyMultiple', [VehicleController::class, 'destroyMultiple'])->name('vehicles.destroyMultiple');
    Route::resource('drivers', DriverController::class);
    Route::post('drivers/destroyMultiple', [DriverController::class, 'destroyMultiple'])->name('drivers.destroyMultiple');
    Route::resource('vendors', VendorController::class);

    // Insurance Companies
    Route::resource('insurance-companies', InsuranceCompanyController::class);

    // Product Management
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('productList', ProductListController::class);

    // Fleet Transactions
    Route::post('dailyMileages/destroyMultiple', [DailyMileageController::class, 'destroyMultiple'])->name('dailyMileages.destroyMultiple');
    Route::resource('dailyMileages', DailyMileageController::class);
    Route::resource('dailyMileageReports', DailyMileageReportController::class);
    Route::resource('dailyFuels', DailyFuelController::class);
    Route::resource('dailyFuelReports', DailyFuelReportController::class);

    // Attendance
    // Filter (POST) route to reuse create() for filtering
    Route::post('driverAttendances/create', [DriversAttendanceController::class, 'create'])->name('driverAttendances.filter');
    Route::post('driverAttendances/destroyMultiple', [DriversAttendanceController::class, 'destroyMultiple'])->name('driverAttendances.destroyMultiple');
    Route::resource('driverAttendances', DriversAttendanceController::class);

    Route::post('vehicleAttendances/create', [VehiclesAttendanceController::class, 'create'])->name('vehicleAttendances.filter');
    Route::post('vehicleAttendances/destroyMultiple', [VehiclesAttendanceController::class, 'destroyMultiple'])->name('vehicleAttendances.destroyMultiple');
    Route::resource('vehicleAttendances', VehiclesAttendanceController::class);



    // Inventory
    Route::resource('products', ProductController::class);
    Route::get('/get-product-details', [ProductController::class, 'getProductDetails'])->name('get-product-details');




    Route::resource('inventoryWarehouses', InventoryWarehouseController::class);



    // Inventory Issuance
    Route::resource('inventoryDemands', InventoryDemandController::class);
    Route::resource('inventoryDispatchs', InventoryDispatchController::class);
    Route::resource('inventoryLargerReports', InventoryLargerReportController::class);

    // Accidents
    Route::resource('accidentDetails', AccidentDetailController::class);
    Route::get('accidentDetails/get-vehicle-info/{vehicleNo}', [AccidentDetailController::class, 'getVehicleInfo'])->name('accidentDetails.getVehicleInfo');
    Route::resource('accidentReports', AccidentReportController::class);

    // Accounts
    Route::resource('clientInvoices', ClientInvoiceController::class);
    Route::resource('cashPayments', CashPaymentController::class);
    Route::resource('bankPayments', BankPaymentController::class);

    // Vehicle Maintenance
    Route::resource('vehicleMaintenances', VehicleMaintenanceController::class);
    Route::resource('vehicleMaintenanceReports', VehicleMaintenanceReportController::class);

    // Attendance
    Route::resource('driverAttendances', DriversAttendanceController::class);
    Route::resource('vehicleAttendances', VehiclesAttendanceController::class);






    // Warehouses & Locations
    Route::resource('locations', LocationController::class);
    Route::resource('ledder_makers', LedderMakerController::class);

    // Drafts Management
    Route::get('/drafts', [App\Http\Controllers\Admin\DraftController::class, 'index'])->name('drafts.index');
    Route::get('/drafts/{draft}/edit', [App\Http\Controllers\Admin\DraftController::class, 'edit'])->name('drafts.edit');
    Route::delete('/drafts/{draft}', [App\Http\Controllers\Admin\DraftController::class, 'destroy'])->name('drafts.destroy');
    Route::get('/drafts/download/{path}', [App\Http\Controllers\Admin\DraftController::class, 'downloadFile'])->name('drafts.download');
    Route::get('/drafts/view/{path}', [App\Http\Controllers\Admin\DraftController::class, 'viewFile'])->name('drafts.view');
    Route::post('/drafts/{draft}/remove-file', [App\Http\Controllers\Admin\DraftController::class, 'removeFile'])->name('drafts.removeFile');

    Route::get('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'index'])->name('master_warehouse_inventory.index');
    Route::get('/master-warehouse-inventory/create', [MasterWarehouseInventoryController::class, 'create'])->name('master_warehouse_inventory.create');
    Route::post('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'store'])->name('master_warehouse_inventory.store');
    Route::post('/master-inventory/assign', [MasterWarehouseInventoryController::class, 'assignStock'])
        ->name('master_warehouse_inventory.assign');

    // Add this line with your other admin routes
    Route::get('/assigned-inventory', [MasterWarehouseInventoryController::class, 'assigned'])
        ->name('assigned_inventory.index');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::get('/warehouses/assign', [WarehousesController::class, 'assignWarehouse'])->name('warehouses.assign');
    Route::post('/warehouses/create', [WarehousesController::class, 'createWarehouse'])->name('warehouses.create');
    Route::post('/warehouses/request-inventory', [WarehousesController::class, 'requestInventory'])->name('warehouses.request_inventory');
    Route::post('/warehouses/issue-inventory', [WarehousesController::class, 'issueInventory'])->name('warehouses.issue_inventory');
});

Route::prefix('master-warehouse')->name('master-warehouse.')->middleware('auth', 'role:master-warehouse')->group(function () {

    Route::get('dashboard', [MasterwarehouseController::class, 'index'])->name('index');

    Route::resource('warehouses', WarehouseController::class);
    Route::resource('productList', ProductListController::class);
    Route::resource('suppliers', SupplierController::class);

    Route::get('/assigned-inventory', [MasterWarehouseInventoryController::class, 'assigned'])
        ->name('assigned_inventory.index');

    //masterwarehouse route
    Route::get('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'index'])->name('master_warehouse_inventory.index');
    Route::get('/master-warehouse-inventory/create', [MasterWarehouseInventoryController::class, 'create'])->name('master_warehouse_inventory.create');
    Route::post('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'store'])->name('master_warehouse_inventory.store');
    Route::post('/master-inventory/assign', [MasterWarehouseInventoryController::class, 'assignStock'])
        ->name('master_warehouse_inventory.assign');

    //purchase routes
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::get(
        'inventory-requests',
        [InventoryRequestController::class, 'index']
    )->name('inventory-requests.index');


    Route::post('inventory-requests/{inventoryRequest}/approve', [InventoryRequestController::class, 'approve'])
        ->name('inventory-requests.approve');

    Route::post('inventory-requests/{inventoryRequest}/reject', [InventoryRequestController::class, 'reject'])
        ->name('inventory-requests.reject');

    Route::resource('jobcarts', JobCartController::class);
    Route::post('/inventory/assign', [MasterWarehouseInventoryController::class, 'assign'])
        ->name('inventory.assign');

    Route::post('jobcarts/update-status', [JobCartController::class, 'updateStatus'])
        ->name('jobcarts.updateStatus');
});

Route::prefix('sub-warehouse')->name('sub-warehouse.')->middleware('auth', 'role:sub-warehouse')->group(function () {
    Route::get('dashboard', [MasterwarehouseController::class, 'index'])->name('index');
    Route::get('/assigned-inventory', [MasterWarehouseInventoryController::class, 'assigned'])
        ->name('assigned_inventory.index');
    Route::get('/master_warehouse_inventory/request_inventory', [MasterWarehouseInventoryController::class, 'requestInventory'])->name('master_warehouse_inventory.request_inventory');

    // Route::post(
    //     '/subwarehouse/inventory/request',
    //     [MasterWarehouseInventoryController::class, 'request']
    // )->name('inventory.request');


    Route::resource(
        'inventory-requests',
        InventoryRequestController::class
    )->only(['index', 'store']); // ✅ FIX HERE

    Route::get('requested-inventory-history', [InventoryRequestController::class, 'requestedInventoryHistory'])->name('master_warehouse_inventory.requested_inventory_history');
});

Route::prefix('sub-warehouse')->name('sub-warehouse.')->middleware('auth', 'role:sub-warehouse')->group(function () {
    Route::get('dashboard', [MasterwarehouseController::class, 'index'])->name('index');

    Route::resource('warehouses', WarehouseController::class);
    Route::resource('productList', ProductListController::class);
    Route::resource('suppliers', SupplierController::class);

    Route::get('/assigned-inventory', [MasterWarehouseInventoryController::class, 'assigned'])
        ->name('assigned_inventory.index');

    //masterwarehouse route
    Route::get('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'index'])->name('master_warehouse_inventory.index');
    Route::get('/master-warehouse-inventory/create', [MasterWarehouseInventoryController::class, 'create'])->name('master_warehouse_inventory.create');
    Route::post('/master-warehouse-inventory', [MasterWarehouseInventoryController::class, 'store'])->name('master_warehouse_inventory.store');
    Route::post('/master-inventory/assign', [MasterWarehouseInventoryController::class, 'assignStock'])
        ->name('master_warehouse_inventory.assign');

    //purchase routes
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
});


Route::prefix('maintainer')->name('maintainer.')->middleware('auth', 'role:maintainer')->group(function () {
    Route::get('dashboard', [MaintainerController::class, 'index'])->name('index');
    Route::get('issues', [MaintainerController::class, 'issues'])->name('issues');
    // Route::get('jobcart', [MaintainerController::class, 'jobcart'])->name('jobcart');
    // Route::post('jobcart', [MaintainerController::class, 'createJobCart'])->name('createjobcart');
    Route::resource('jobcarts', JobCartController::class);
});

Route::get('/clear', function () {
    Auth::logout();
    Session::flush();
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return '✅ Config, route, cache & view cleared.';
});
