<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class FrontendAcl
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $contexts
     */
    public function handle(Request $request, Closure $next, ...$contexts): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $map = config('frontend-acl.map', []);

        if (! empty($contexts)) {
            $filtered = [];
            foreach ($contexts as $context) {
                if (isset($map[$context])) {
                    $filtered[$context] = $map[$context];
                }
            }
            $map = $filtered;
        }

        $user = Auth::user();
        $permissions = [];

        foreach ($map as $key => $permission) {
            $permissions[$key] = $user->can($permission);
        }

        $request->attributes->set('frontendAcl', $permissions);
        View::share('frontendAcl', $permissions);

        return $next($request);
    }
}

