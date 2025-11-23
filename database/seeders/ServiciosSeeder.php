<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            [
                'codigo' => 'CONS-01',
                'nombre' => 'Consulta General',
                'descripcion' => 'Consulta veterinaria general para diagnóstico y chequeo',
                'tipo' => 'consulta',
                'duracion_minutos' => 30,
                'precio' => 50.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'VAC-01',
                'nombre' => 'Vacuna Antirrábica',
                'descripcion' => 'Vacunación contra la rabia',
                'tipo' => 'vacuna',
                'duracion_minutos' => 15,
                'precio' => 35.00,
                'requiere_vacuna_info' => true,
            ],
            [
                'codigo' => 'VAC-02',
                'nombre' => 'Vacuna Triple Felina',
                'descripcion' => 'Vacuna contra panleucopenia, rinotraqueitis y calicivirus',
                'tipo' => 'vacuna',
                'duracion_minutos' => 15,
                'precio' => 40.00,
                'requiere_vacuna_info' => true,
            ],
            [
                'codigo' => 'VAC-03',
                'nombre' => 'Vacuna Séxtuple Canina',
                'descripcion' => 'Protección contra distemper, hepatitis, parvovirus, parainfluenza, leptospirosis',
                'tipo' => 'vacuna',
                'duracion_minutos' => 15,
                'precio' => 45.00,
                'requiere_vacuna_info' => true,
            ],
            [
                'codigo' => 'DESP-01',
                'nombre' => 'Desparasitación Interna',
                'descripcion' => 'Tratamiento antiparasitario interno',
                'tipo' => 'tratamiento',
                'duracion_minutos' => 10,
                'precio' => 25.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'DESP-02',
                'nombre' => 'Desparasitación Externa',
                'descripcion' => 'Tratamiento contra pulgas, garrapatas y ácaros',
                'tipo' => 'tratamiento',
                'duracion_minutos' => 15,
                'precio' => 30.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'BAÑO-01',
                'nombre' => 'Baño Medicado',
                'descripcion' => 'Baño con shampoo medicado para problemas dermatológicos',
                'tipo' => 'baño',
                'duracion_minutos' => 45,
                'precio' => 60.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'BAÑO-02',
                'nombre' => 'Baño y Corte',
                'descripcion' => 'Baño completo con corte de pelo',
                'tipo' => 'baño',
                'duracion_minutos' => 60,
                'precio' => 80.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'CIR-01',
                'nombre' => 'Esterilización/Castración',
                'descripcion' => 'Cirugía de esterilización o castración',
                'tipo' => 'cirugía',
                'duracion_minutos' => 90,
                'precio' => 250.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'CIR-02',
                'nombre' => 'Cirugía Menor',
                'descripcion' => 'Procedimiento quirúrgico menor',
                'tipo' => 'cirugía',
                'duracion_minutos' => 60,
                'precio' => 180.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'EXAM-01',
                'nombre' => 'Análisis de Sangre Completo',
                'descripcion' => 'Hemograma y perfil bioquímico',
                'tipo' => 'otro',
                'duracion_minutos' => 20,
                'precio' => 120.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'EXAM-02',
                'nombre' => 'Radiografía',
                'descripcion' => 'Estudio radiográfico',
                'tipo' => 'otro',
                'duracion_minutos' => 30,
                'precio' => 100.00,
                'requiere_vacuna_info' => false,
            ],
            [
                'codigo' => 'CONS-02',
                'nombre' => 'Consulta de Emergencia',
                'descripcion' => 'Atención veterinaria de urgencia',
                'tipo' => 'consulta',
                'duracion_minutos' => 45,
                'precio' => 150.00,
                'requiere_vacuna_info' => false,
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::firstOrCreate(
                ['codigo' => $servicio['codigo']],
                $servicio
            );
        }

        $this->command->info('✅ Servicios básicos creados exitosamente');
    }
}
