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
        // Permitimos que usuarios autenticados puedan llamar al endpoint.
        // El filtrado por cliente (solo ver sus mascotas) se aplicará en el
        // controlador (index) para clientes; roles como 'veterinario'/'recepcion'
        // podrán ver todas las mascotas.
        return (bool) $user;
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
        // Si el usuario es cliente, comparar con su perfil cliente->id
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            return $cliente && $mascota->cliente_id === $cliente->id;
        }

        // Veterinario/reception/admin pueden ver cualquier mascota
        return in_array($user->tipo_usuario, ['veterinario', 'recepcion', 'admin']);
    }
    
    // ... el resto de métodos (create, update, delete)
    public function create(User $user)
    {
        return in_array($user->tipo_usuario, ['cliente', 'veterinario', 'recepcion']);
    }

    public function update(User $user, Mascota $mascota)
    {
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            return $cliente && $mascota->cliente_id === $cliente->id;
        }

        return in_array($user->tipo_usuario, ['veterinario', 'recepcion']);
    }

    public function delete(User $user, Mascota $mascota)
    {
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            return $cliente && $mascota->cliente_id === $cliente->id;
        }

        return in_array($user->tipo_usuario, ['veterinario', 'recepcion']);
    }
}