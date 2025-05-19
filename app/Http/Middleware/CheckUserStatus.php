<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Get fresh user data from database
            $user = User::find(Auth::id());
            
            // If user is inactive, log them out
            if ($user && $user->status === 'inactive') {
                Auth::logout();
                Session::flush();
                
                return redirect()->route('tenant.login')
                    ->with('error', 'Your account has been archived. Please contact your administrator for assistance.');
            }
        }
        
        return $next($request);
    }
} 