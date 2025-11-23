<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mascota;
use Illuminate\Support\Str;

class MascotasQRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualizar todas las mascotas existentes con QR
        $mascotas = Mascota::whereNull('qr_code')->get();

        if ($mascotas->isEmpty()) {
            $this->command->info('✅ Todas las mascotas ya tienen QR');
            return;
        }

        $this->command->info("🔄 Generando QR para {$mascotas->count()} mascotas...");

        foreach ($mascotas as $mascota) {
            $mascota->update([
                'qr_code' => 'VETCARE_PET_' . Str::uuid(),
                'alergias' => $this->randomAllergies(),
                'tipo_sangre' => $this->randomBloodType(),
            ]);
        }

        $this->command->info('✅ QR codes generados para ' . $mascotas->count() . ' mascotas');
    }

    private function randomAllergies()
    {
        $allergies = ['Ninguna', 'Penicilina', 'Polen', 'Pulgas', 'Alimentos', null];
        return $allergies[array_rand($allergies)];
    }

    private function randomBloodType()
    {
        $types = ['DEA 1.1+', 'DEA 1.1-', 'DEA 1.2+', 'DEA 3+', 'A', 'B', 'AB', null];
        return $types[array_rand($types)];
    }
}
