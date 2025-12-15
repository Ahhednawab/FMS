<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (Auth::check() && Auth::user()->role->slug === $role) {

            // Example: assuming user has role relation with slug
            $roleSlug = Auth::user()->role->slug;

            // Share role slug with all views
            view()->share('roleSlug', $roleSlug);

            // Or attach to request
            $request->attributes->set('roleSlug', $roleSlug);
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
