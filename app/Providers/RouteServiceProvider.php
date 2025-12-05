<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Route::get('/dashboard', function () {
            $user = auth()->user();

            return redirect()->intended(
                $user->role === 'admin' ? '/admin-dashboard' : ($user->role === 'manager' ? '/manager-dashboard' : '/user-dashboard')
            );
        })->middleware(['auth'])->name('dashboard');
    }
}
