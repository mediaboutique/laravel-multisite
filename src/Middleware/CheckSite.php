<?php

namespace MediaBoutique\Multisite\Middleware;

use MediaBoutique\Multisite\Facades\Multisite;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;

class CheckSite
{
    public function handle(Request $request, Closure $next, string $alias)
    {
        if (Multisite::alias() !== $alias) {
            abort(403);
        }

        return $next($request);
    }
}
