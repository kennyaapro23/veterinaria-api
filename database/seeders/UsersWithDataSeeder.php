<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Veterinario;
use App\Models\Mascota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersWithDataSeeder extends Seeder
{
    /**
     * Seed usuarios de prueba con roles y datos relacionados
     */
    public function run(): void
    {
        // 1. USUARIO CLIENTE con 2 mascotas
        $userCliente = User::create([
            'name' => 'Juan Pérez',
            'email' => 'cliente@veterinaria.com',
            'password' => Hash::make('password123'),
            'telefono' => '+34612345678',
            'tipo_usuario' => 'cliente',
            'email_verified_at' => now(),
        ]);
        
        $userCliente->assignRole('cliente');

        $cliente = Cliente::create([
            'user_id' => $userCliente->id,
            'nombre' => 'Juan Pérez',
            'telefono' => '+34612345678',
            'email' => 'cliente@veterinaria.com',
            'documento_tipo' => 'DNI',
            'documento_num' => '12345678A',
            'direccion' => 'Calle Principal 123, Madrid',
            'notas' => 'Cliente regular, prefiere citas por la mañana',
        ]);

        // Mascotas del cliente
        Mascota::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Max',
            'especie' => 'Perro',
            'raza' => 'Labrador Retriever',
            'sexo' => 'macho',
            'fecha_nacimiento' => '2020-03-15',
            'color' => 'Dorado',
            'chip_id' => '981234567890123',
        ]);

        Mascota::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Luna',
            'especie' => 'Gato',
            'raza' => 'Siamés',
            'sexo' => 'hembra',
            'fecha_nacimiento' => '2021-08-20',
            'color' => 'Crema con puntos oscuros',
            'chip_id' => '981234567890456',
        ]);

        // 2. USUARIO VETERINARIO
        $userVeterinario = User::create([
            'name' => 'Dra. María García',
            'email' => 'veterinario@veterinaria.com',
            'password' => Hash::make('password123'),
            'telefono' => '+34687654321',
            'tipo_usuario' => 'veterinario',
            'email_verified_at' => now(),
        ]);
        
        $userVeterinario->assignRole('veterinario');

        Veterinario::create([
            'user_id' => $userVeterinario->id,
            'nombre' => 'Dra. María García',
            'matricula' => 'VET-2024-001',
            'especialidad' => 'Medicina General y Cirugía',
            'telefono' => '+34687654321',
            'email' => 'veterinario@veterinaria.com',
            'disponibilidad' => [
                'lunes' => ['09:00-13:00', '16:00-20:00'],
                'martes' => ['09:00-13:00', '16:00-20:00'],
                'miercoles' => ['09:00-13:00', '16:00-20:00'],
                'jueves' => ['09:00-13:00', '16:00-20:00'],
                'viernes' => ['09:00-13:00', '16:00-19:00'],
            ],
        ]);

        // 3. USUARIO RECEPCIÓN
        $userRecepcion = User::create([
            'name' => 'Ana Martínez',
            'email' => '        ',
            'password' => Hash::make('password123'),
            'telefono' => '+34656789012',
            'tipo_usuario' => 'recepcion',
            'email_verified_at' => now(),
        ]);
        
        $userRecepcion->assignRole('recepcion');

        $this->command->info('✅ Usuarios creados exitosamente:');
        $this->command->info('   📧 Cliente: cliente@veterinaria.com / password123');
        $this->command->info('   👤 Usuario: Juan Pérez (con 2 mascotas: Max y Luna)');
        $this->command->info('');
        $this->command->info('   📧 Veterinario: veterinario@veterinaria.com / password123');
        $this->command->info('   👩‍⚕️ Usuario: Dra. María García (Matrícula: VET-2024-001)');
        $this->command->info('');
        $this->command->info('   📧 Recepción: recepcion@veterinaria.com / password123');
        $this->command->info('   👤 Usuario: Ana Martínez');
    }
}
