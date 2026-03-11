<?php

namespace App\Policies;

use App\Models\PersonaAutorizada;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonaAutorizadaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de personas autorizadas
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PersonaAutorizada $personaAutorizada): bool
    {
        // Todos los usuarios autenticados pueden ver una persona autorizada específica
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear personas autorizadas
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PersonaAutorizada $personaAutorizada): bool
    {
        // Todos los usuarios autenticados pueden actualizar personas autorizadas
        // En el futuro, aquí se puede agregar lógica más específica
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PersonaAutorizada $personaAutorizada): bool
    {
        // Todos los usuarios autenticados pueden eliminar personas autorizadas
        // En el futuro, aquí se puede agregar lógica más específica
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PersonaAutorizada $personaAutorizada): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PersonaAutorizada $personaAutorizada): bool
    {
        return true;
    }
}
