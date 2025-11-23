<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden importante: primero roles, luego usuarios
        $this->call([
            RolesSeeder::class,
            ServiciosSeeder::class,
            UsersWithDataSeeder::class,
        ]);
    }
}
