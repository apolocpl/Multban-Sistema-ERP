<?php

namespace App\Policies;

use App\Models\Multban\Cliente\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the cliente.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        return $this->belongsToUserEmpresa($user, $cliente);
    }

    /**
     * Determine whether the user can update the cliente.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        return $this->belongsToUserEmpresa($user, $cliente);
    }

    /**
     * Determine whether the user can delete the cliente.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        return $this->belongsToUserEmpresa($user, $cliente);
    }

    /**
     * Determine whether the user can change the status of the cliente.
     */
    public function updateStatus(User $user, Cliente $cliente): bool
    {
        return $this->belongsToUserEmpresa($user, $cliente);
    }

    /**
     * Determine whether the user can manage cards or medical records tied to the cliente.
     */
    public function manageRelatedData(User $user, Cliente $cliente): bool
    {
        return $this->belongsToUserEmpresa($user, $cliente);
    }

    /**
     * Helper to verify the cliente belongs to one of the user's empresas.
     */
    protected function belongsToUserEmpresa(User $user, Cliente $cliente): bool
    {
        $empresaId = $user->emp_id;

        if (empty($empresaId)) {
            return false;
        }

        return $cliente->empresa()
            ->where('tbdm_clientes_emp.emp_id', $empresaId)
            ->exists();
    }
}

