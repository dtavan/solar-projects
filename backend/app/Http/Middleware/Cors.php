<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class Cors
{
    public function handle($request, Closure $next, $guard = null)
    {
        return $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
