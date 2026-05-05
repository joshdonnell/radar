<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use JoshDonnell\Radar\Support\Config;
use Symfony\Component\HttpFoundation\Response;

final class Authorize
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        Gate::authorize(Config::authorizationGate());

        return $next($request);
    }
}
