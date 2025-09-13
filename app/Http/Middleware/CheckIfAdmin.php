<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and has the 'admin' role
        if (Auth::guard('api')->check() && Auth::user()->hasRole('admin')) {
            return $next($request);
        }

        // If not an admin, return a 403 Forbidden response
        return response()->json(['message' => 'Forbidden'], 403);
    }
}
