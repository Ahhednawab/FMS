<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        dd(Auth::user()->role_id);
        if (Auth::check() && Auth::user()->role_id === $role) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
