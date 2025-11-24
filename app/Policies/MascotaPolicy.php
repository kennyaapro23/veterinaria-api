<?php

namespace App\Policies;

use App\Models\Mascota;
use App\Models\User;

class MascotaPolicy
{
    /**
     * Determine whether the user can view any mascotas.
     */
    public function viewAny(User $user): bool
    {
        // Veterinario and recepcion can view all; cliente can view their own (controller filters)
        return in_array($user->tipo_usuario, ['veterinario', 'recepcion', 'cliente']);
    }

    /**
     * Determine whether the user can view the mascota.
     */
    public function view(User $user, Mascota $mascota): bool
    {
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            return $cliente && $mascota->cliente_id === $cliente->id;
        }

        // Veterinario and recepcion can view any
        return in_array($user->tipo_usuario, ['veterinario', 'recepcion']);
    }

    /**
     * Determine whether the user can create mascotas.
     */
    public function create(User $user): bool
    {
        // Clients can create for themselves; veterinario and recepcion can create for any (by specifying cliente_id)
        return in_array($user->tipo_usuario, ['cliente', 'veterinario', 'recepcion']);
    }

    /**
     * Determine whether the user can update the mascota.
     */
    public function update(User $user, Mascota $mascota): bool
    {
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            return $cliente && $mascota->cliente_id === $cliente->id;
        }

        return in_array($user->tipo_usuario, ['veterinario', 'recepcion']);
    }

    /**
     * Determine whether the user can delete the mascota.
     */
    public function delete(User $user, Mascota $mascota): bool
    {
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            return $cliente && $mascota->cliente_id === $cliente->id;
        }

        return in_array($user->tipo_usuario, ['veterinario', 'recepcion']);
    }
}
