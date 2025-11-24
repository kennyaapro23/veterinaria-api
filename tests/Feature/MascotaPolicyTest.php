<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Mascota;

class MascotaPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_only_lists_their_mascotas()
    {
        $user = User::factory()->create(['tipo_usuario' => 'cliente']);
        $cliente = Cliente::create([
            'user_id' => $user->id,
            'nombre' => 'Cliente Uno',
            'email' => 'cliente1@example.com'
        ]);

        $otherCliente = Cliente::create([
            'nombre' => 'Cliente Dos',
            'email' => 'cliente2@example.com'
        ]);

        $mine = Mascota::create(['cliente_id' => $cliente->id, 'nombre' => 'MiMascota', 'especie' => 'perro', 'sexo' => 'macho']);
        $other = Mascota::create(['cliente_id' => $otherCliente->id, 'nombre' => 'OtraMascota', 'especie' => 'gato', 'sexo' => 'hembra']);

        $this->actingAs($user, 'sanctum');

        $resp = $this->getJson('/api/mascotas');
        $resp->assertStatus(200);
        $resp->assertJsonFragment(['nombre' => 'MiMascota']);
        $resp->assertJsonMissing(['nombre' => 'OtraMascota']);
    }

    public function test_cliente_crear_mascota_asigna_su_cliente_id()
    {
        $user = User::factory()->create(['tipo_usuario' => 'cliente']);
        $cliente = Cliente::create([
            'user_id' => $user->id,
            'nombre' => 'Cliente Uno',
            'email' => 'cliente3@example.com'
        ]);

        $otherCliente = Cliente::create([
            'nombre' => 'Cliente Dos',
            'email' => 'cliente4@example.com'
        ]);

        $this->actingAs($user, 'sanctum');

        $resp = $this->postJson('/api/mascotas', [
            'nombre' => 'CreadaPorCliente',
            'especie' => 'perro',
            'sexo' => 'macho',
            // intentar forzar cliente_id
            'cliente_id' => $otherCliente->id,
        ]);

        $resp->assertStatus(201);
        $resp->assertJsonPath('mascota.cliente_id', $cliente->id);
    }

    public function test_veterinario_puede_listar_todas_las_mascotas()
    {
        $vet = User::factory()->create(['tipo_usuario' => 'veterinario']);

        $clienteA = Cliente::create(['nombre' => 'A', 'email' => 'a@example.com']);
        $clienteB = Cliente::create(['nombre' => 'B', 'email' => 'b@example.com']);

        $m1 = Mascota::create(['cliente_id' => $clienteA->id, 'nombre' => 'A', 'especie' => 'perro', 'sexo' => 'macho']);
        $m2 = Mascota::create(['cliente_id' => $clienteB->id, 'nombre' => 'B', 'especie' => 'gato', 'sexo' => 'hembra']);

        $this->actingAs($vet, 'sanctum');

        $resp = $this->getJson('/api/mascotas');
        $resp->assertStatus(200);
        $resp->assertJsonFragment(['nombre' => 'A']);
        $resp->assertJsonFragment(['nombre' => 'B']);
    }
}
