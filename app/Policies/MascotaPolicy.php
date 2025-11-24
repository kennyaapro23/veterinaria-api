<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Mascota;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MascotaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models (GET /api/mascotas).
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // ----------------------------------------------------
        // ¡SOLUCIÓN! Retornar true si el usuario está autenticado.
        // Si necesitas lógica de roles (Admin/Vet), la puedes añadir aquí:
        // return $user->esAdmin() || $user->esVeterinario();
        // Pero para salir del error 403, retornamos true si está autenticado:
        // ----------------------------------------------------
        return true; 
    }

    /**
     * Determine whether the user can view the model (GET /api/mascotas/{id}).
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Mascota  $mascota
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Mascota $mascota)
    {
        // Esto permite ver la mascota si es el dueño O si tiene permisos generales
        return $user->id === $mascota->cliente_id || $user->esAdmin() || $user->esVeterinario();
    }
    
    // ... el resto de métodos (create, update, delete)
    public function create(User $user) { return true; }
    public function update(User $user, Mascota $mascota) { return $user->id === $mascota->cliente_id; }
    public function delete(User $user, Mascota $mascota) { return $user->id === $mascota->cliente_id; }
}