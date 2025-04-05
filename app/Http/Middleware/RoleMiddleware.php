<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $userRole = auth()->user()->role;

        if ($userRole === $role) {
            return $next($request);
        }

        // Redirect based on role
        if ($userRole === 'admin') {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'Access denied. This area is for faculty only.');
        }
        
        if ($userRole === 'user') {
            return redirect()->route('tenant.faculty.dashboard')
                ->with('error', 'Access denied. This area is for administrators only.');
        }

        return redirect()->back()->with('error', 'Unauthorized access.');
    }
}