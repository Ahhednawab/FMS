<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\LedderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\InventoryWarehouseController;
use App\Models\Role;

Route::get('/clear', function () {
    Auth::logout();
    Session::flush();
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');

    return "cleared";
});

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [CustomLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomLoginController::class, 'login']);
});

Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.index');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/countries', [CountriesController::class, 'index'])->name('countries.index');
    Route::resource('countries', CountriesController::class);
    Route::resource('cities', \App\Http\Controllers\Admin\CityController::class);
    Route::resource('/warehouses', \App\Http\Controllers\Admin\WarehouseController::class);
    Route::resource('/locations', \App\Http\Controllers\Admin\LocationController::class);
    Route::resource('/ledder_makers', \App\Http\Controllers\Admin\LedderMakerController::class);

    // Master Data
    Route::resource('/drivers', \App\Http\Controllers\Admin\DriverController::class);
    Route::resource('/cars', \App\Http\Controllers\Admin\CarController::class);

    // Inventory
    Route::resource('/products', ProductController::class);
    Route::resource('/inventory-warehouses', InventoryWarehouseController::class);

    // Fleet Transactions
    Route::get('/tracker-mileage', [TrackerMileageController::class, 'index'])->name('tracker_mileage.index');
    Route::get('/daily-mileage', [DailyMileageController::class, 'index'])->name('daily_mileage.index');
    Route::resource('fuel_mileage', \App\Http\Controllers\Admin\DailyFuelMileageController::class);

    // Inventory Issuance
    Route::resource('inventory_demand', \App\Http\Controllers\Admin\InventoryDemandController::class);
    Route::resource('inventory_dispatch', \App\Http\Controllers\Admin\InventoryDispatchController::class);
    Route::resource('inventory_report', \App\Http\Controllers\Admin\InventoryLargerReportController::class);

    // Accidents
    Route::resource('accident_details', \App\Http\Controllers\Admin\AccidentDetailController::class);
    Route::resource('accident_reports', \App\Http\Controllers\Admin\AccidentReportController::class);

    // Accounts
    Route::resource('client_invoices', \App\Http\Controllers\Admin\ClientInvoiceController::class);
    Route::resource('cash_payments', \App\Http\Controllers\Admin\CashPaymentController::class);
    Route::resource('bank_payments', \App\Http\Controllers\Admin\BankPaymentController::class);

    // Car Maintenance
    Route::resource('car_maintenance', \App\Http\Controllers\Admin\CarMaintenanceController::class);
    Route::resource('car_maintenance_report', \App\Http\Controllers\Admin\CarMaintenanceReportController::class);

    // Attendance
    Route::resource('driver_attendance', \App\Http\Controllers\Admin\DriversAttendanceController::class);
    Route::resource('vehicle_attendance', \App\Http\Controllers\Admin\VehiclesAttendanceController::class);
});
