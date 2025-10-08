<?php

namespace App\Http\Middleware;

use App\Actions\DbSysClientConnection;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDbSysClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app('redirect')->setIntendedUrl(str_replace(url('/'), '', url()->previous()));
        if (Auth::check()) {
            app(DbSysClientConnection::class, [
                'user' => $request->user(),
            ])->execute();
        } else {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'type'    => 'error',
                    'message' => 'CSRF token mismatch.',
                ], Response::HTTP_UNAUTHORIZED);
            } else {
                return redirect('/login')->with('error', 'Sua sessão expirou, é preciso fazer o login novamente.');
            }
        }

        return $next($request);
    }
}
