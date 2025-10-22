<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function __construct(private readonly TenantManager $tenantManager) {}

    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $keys
     */
    public function handle(Request $request, Closure $next, ...$keys): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $tenantId = $this->tenantManager->id();
        if ($tenantId === null) {
            return $next($request);
        }

        $parameterKeys = $this->resolveKeys($keys);

        foreach ($parameterKeys as $key) {
            $value = $this->extractValue($request, $key);

            if ($value === null) {
                continue;
            }

            if ($value !== $tenantId) {
                abort(Response::HTTP_FORBIDDEN, 'Acesso negado para esta empresa.');
            }
        }

        return $next($request);
    }

    /**
     * Decide which keys will be validated.
     */
    private function resolveKeys(array $keys): array
    {
        if (! empty($keys)) {
            return $keys;
        }

        return config('tenant.protected_parameters', []);
    }

    /**
     * Extract the value for a given key from route parameters, query string or request payload.
     */
    private function extractValue(Request $request, string $key): ?int
    {
        if ($request->route()) {
            $routeValue = $request->route($key);
            if (! is_null($routeValue)) {
                $normalized = $this->normalizeValue($routeValue);
                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        if ($request->query->has($key)) {
            $normalized = $this->normalizeValue($request->query($key));
            if ($normalized !== null) {
                return $normalized;
            }
        }

        if ($request->request->has($key)) {
            $normalized = $this->normalizeValue($request->input($key));
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * Normalize mixed values (including route models) to tenant id.
     */
    private function normalizeValue(mixed $value): ?int
    {
        if ($value instanceof Model) {
            foreach (config('tenant.model_tenant_attributes', []) as $attribute) {
                if (! is_null($modelValue = data_get($value, $attribute))) {
                    return (int) $modelValue;
                }
            }

            return null;
        }

        if (is_array($value)) {
            foreach ($value as $possible) {
                $normalized = $this->normalizeValue($possible);
                if ($normalized !== null) {
                    return $normalized;
                }
            }

            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
