<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
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

    public function boot(): void
    {
        Paginator::useBootstrap();

        $this->registerPolicies(); // ✅ now works

        Paginator::useBootstrap();

        Gate::define('access-admin-dashboard', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('access-dashboard', function (User $user) {
            return $user->hasRole(['admin', 'manager']);
        });
    }
}
