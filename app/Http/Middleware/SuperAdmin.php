<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        abort_403(!is_superadmin());
        return $next($request);
    }
}
