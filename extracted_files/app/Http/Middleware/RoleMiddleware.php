<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // The roles correspond to the auth guard names: 'admin', 'guru', 'wali_kelas'
        foreach ($roles as $role) {
            if (\Illuminate\Support\Facades\Auth::guard($role)->check()) {
                // Set the active guard for the request
                \Illuminate\Support\Facades\Auth::shouldUse($role);
                return $next($request);
            }
        }

        return redirect()->route('login');
    }
}
