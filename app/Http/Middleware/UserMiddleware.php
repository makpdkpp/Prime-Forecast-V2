<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (int) auth()->user()->role_id === 3) {
            return $next($request);
        }
        
        // If logged in but wrong role, redirect to appropriate dashboard
        if (auth()->check()) {
            $roleId = (int) auth()->user()->role_id;
            return match ($roleId) {
                1 => redirect()->route('admin.dashboard'),
                2 => redirect()->route('teamadmin.dashboard'),
                default => redirect()->route('login'),
            };
        }
        
        return redirect()->route('login')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}
