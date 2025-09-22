<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TeacherMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('web')->check()) {
            return redirect('/login')->with('error', 'Unauthorized access.');
        }

        $response = $next($request);

        // Prevent browser from caching protected pages
        return $response->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate')
                        ->header('Pragma','no-cache')
                        ->header('Expires','Sat, 01 Jan 1990 00:00:00 GMT');
    }
}
