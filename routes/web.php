<?php

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




Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.index');
    }
    return redirect()->route('login');
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
    ->middleware(['auth', 'role:admin'])
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
    Route::resource('drivers', DriverController::class);
    Route::resource('vendors', VendorController::class);

    // Inventory
    Route::resource('products', ProductController::class);
    Route::resource('inventoryWarehouses', InventoryWarehouseController::class);

    // Fleet Transactions
    Route::resource('trackerMileages', TrackerMileageController::class);
    Route::resource('dailyMileages', DailyMileageController::class);
    Route::resource('dailyFuelMileages', DailyFuelMileageController::class);

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
});


Route::get('/clear', function () {
    Auth::logout();
    Session::flush();
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');

    return "cleared";
});