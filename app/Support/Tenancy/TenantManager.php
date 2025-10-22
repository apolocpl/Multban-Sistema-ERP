<?php

namespace App\Support\Tenancy;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantManager
{
    /**
     * Retrieve the current authenticated tenant identifier (empresa).
     */
    public function id(): ?int
    {
        $user = $this->user();

        if (! $user || ! isset($user->emp_id)) {
            return null;
        }

        return (int) $user->emp_id;
    }

    /**
     * Ensure the provided tenant id matches the authenticated tenant.
     */
    public function ensure(?int $tenantId = null): int
    {
        $current = $this->id();

        if ($current === null) {
            throw new HttpException(403, 'Usuário sem contexto de empresa.');
        }

        if ($tenantId !== null && $current !== (int) $tenantId) {
            throw new HttpException(403, 'Acesso não autorizado para esta empresa.');
        }

        return $current;
    }

    /**
     * Resolve current authenticated user.
     */
    private function user(): ?Authenticatable
    {
        return Auth::user();
    }
}

