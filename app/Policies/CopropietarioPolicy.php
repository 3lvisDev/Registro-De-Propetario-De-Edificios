<?php

namespace App\Policies;

use App\Models\Copropietario;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CopropietarioPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de copropietarios
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Copropietario $copropietario): bool
    {
        // Todos los usuarios autenticados pueden ver un copropietario específico
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear copropietarios
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Copropietario $copropietario): bool
    {
        // Todos los usuarios autenticados pueden actualizar copropietarios
        // En el futuro, aquí se puede agregar lógica más específica
        // Por ejemplo: return $user->hasRole('admin') || $user->id === $copropietario->user_id;
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Copropietario $copropietario): bool
    {
        // Todos los usuarios autenticados pueden eliminar copropietarios
        // En el futuro, aquí se puede agregar lógica más específica
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Copropietario $copropietario): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Copropietario $copropietario): bool
    {
        return true;
    }
}
