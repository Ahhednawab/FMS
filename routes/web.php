<?php

use App\Http\Controllers\Admin\MasterWarehouseInventoryController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\WarehousesController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\LedderMakerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\InventoryWarehouseController;
use App\Http\Controllers\Admin\TrackerMileageController;
use App\Http\Controllers\Admin\DailyMileageController;
use App\Http\Controllers\Admin\DailyFuelMileageController;
use App\Http\Controllers\Admin\InventoryDemandController;
use App\Http\Controllers\Admin\InventoryDispatchController;
use App\Http\Controllers\Admin\InventoryLargerReportController;
use App\Http\Controllers\Admin\AccidentDetailController;
use App\Http\Controllers\Admin\AccidentReportController;
use App\Http\Controllers\Admin\ClientInvoiceController;
use App\Http\Controllers\Admin\CashPaymentController;
use App\Http\Controllers\Admin\BankPaymentController;
use App\Http\Controllers\Admin\VehicleMaintenanceController;
use App\Http\Controllers\Admin\VehicleMaintenanceReportController;
use App\Http\Controllers\Admin\DriversAttendanceController;
use App\Http\Controllers\Admin\VehiclesAttendanceController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\IbcController;
use App\Http\Controllers\Admin\ProductListController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DailyMileageReportController;
use App\Http\Controllers\Admin\DailyFuelController;
use App\Http\Controllers\Admin\DailyFuelReportController;
use App\Http\Controllers\Admin\InsuranceCompanyController;

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

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    //General
    Route::resource('users', UserController::class);
    Route::resource('cities', CityController::class);
    Route::resource('stations', StationController::class);
    Route::resource('ibcCenters', IbcController::class);
    Route::resource('warehouses', WarehouseController::class);

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
    Route::resource('dailyMileages', DailyMileageController::class);
    Route::resource('dailyMileageReports', DailyMileageReportController::class);
    Route::resource('dailyFuels', DailyFuelController::class);
    Route::resource('dailyFuelReports', DailyFuelReportController::class);

    // Attendance
    // Filter (POST) route to reuse create() for filtering
    Route::post('driverAttendances/create', [DriversAttendanceController::class, 'create'])->name('driverAttendances.filter');
    Route::resource('driverAttendances', DriversAttendanceController::class);

    Route::post('vehicleAttendances/create', [VehiclesAttendanceController::class, 'create'])->name('vehicleAttendances.filter');
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

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::post('/warehouses/create', [WarehousesController::class, 'createWarehouse'])->name('warehouses.create');
    Route::post('/warehouses/request-inventory', [WarehousesController::class, 'requestInventory'])->name('warehouses.request_inventory');
    Route::post('/warehouses/issue-inventory', [WarehousesController::class, 'issueInventory'])->name('warehouses.issue_inventory');
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
