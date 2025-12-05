<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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

        $this->registerPolicies(); // ✅ now works

        Gate::define('access-admin-dashboard', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('access-dashboard', function (User $user) {
            return $user->hasRole(['admin', 'manager']);
        });
    }
}
