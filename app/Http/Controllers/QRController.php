<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\QRScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QRController extends Controller
{
    /**
     * 🔍 Buscar información por código QR (NUEVO - por qr_code)
     * 
     * @param string $qrCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookup($qrCode)
    {
        try {
            // Validar formato de QR
            if (!str_starts_with($qrCode, 'VETCARE_')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código QR inválido'
                ], 400);
            }

            // Buscar mascota por QR
            $mascota = Mascota::with([
                'cliente',
                'historialMedicos' => function($query) {
                    $query->orderBy('fecha', 'desc')->limit(10)->with('realizadoPor');
                },
                'citas' => function($query) {
                    $query->orderBy('fecha', 'desc')->limit(5)->with('veterinario');
                }
            ])
            ->porQR($qrCode)
            ->first();

            if (!$mascota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mascota no encontrada'
                ], 404);
            }

            // Registrar el escaneo (auditoría)
            QRScanLog::registrar($qrCode, auth()->id());

            // Obtener información del dueño
            $owner = $mascota->cliente;

            // Preparar respuesta con toda la información
            return response()->json([
                'success' => true,
                'pet' => [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'especie' => $mascota->especie,
                    'raza' => $mascota->raza,
                    'sexo' => $mascota->sexo,
                    'fecha_nacimiento' => $mascota->fecha_nacimiento,
                    'color' => $mascota->color,
                    'chip_id' => $mascota->chip_id,
                    'foto_url' => $mascota->foto_url,
                    'qr_code' => $mascota->qr_code,
                    'alergias' => $mascota->alergias,
                    'condiciones_medicas' => $mascota->condiciones_medicas,
                    'tipo_sangre' => $mascota->tipo_sangre,
                    'microchip' => $mascota->microchip,
                    'edad' => $mascota->edad,
                ],
                'owner' => [
                    'id' => $owner->id,
                    'nombre' => $owner->nombre,
                    'telefono' => $owner->telefono,
                    'email' => $owner->email,
                    'direccion' => $owner->direccion,
                ],
                'historial' => $mascota->historialMedicos->map(function($record) {
                    return [
                        'id' => $record->id,
                        'fecha' => $record->fecha,
                        'tipo' => $record->tipo,
                        'diagnostico' => $record->diagnostico,
                        'tratamiento' => $record->tratamiento,
                        'observaciones' => $record->observaciones,
                        'veterinario' => $record->realizadoPor ? [
                            'id' => $record->realizadoPor->id,
                            'nombre' => $record->realizadoPor->nombre,
                        ] : null,
                    ];
                }),
                'ultimas_citas' => $mascota->citas->map(function($cita) {
                    return [
                        'id' => $cita->id,
                        'fecha' => $cita->fecha,
                        'motivo' => $cita->motivo,
                        'estado' => $cita->estado,
                        'veterinario' => $cita->veterinario ? [
                            'id' => $cita->veterinario->id,
                            'nombre' => $cita->veterinario->nombre,
                        ] : null,
                    ];
                }),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar información: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📱 Generar QR para una mascota (MEJORADO)
     * 
     * @param int $mascotaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateMascotaQR($id)
    {
        try {
            $mascota = Mascota::findOrFail($id);

            // Si no tiene QR, generarlo
            if (empty($mascota->qr_code)) {
                $mascota->regenerarQR();
            }

            return response()->json([
                'success' => true,
                'qr_code' => $mascota->qr_code,
                'url' => url("/api/qr/lookup/{$mascota->qr_code}"),
                'mascota_id' => $mascota->id,
                'mascota_nombre' => $mascota->nombre,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 👤 Generar QR para un cliente
     */
    public function generateClienteQR($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            $qrCode = "VETCARE_CLIENT_{$cliente->id}";

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
                'url' => url("/api/qr/lookup/{$qrCode}"),
                'cliente_id' => $cliente->id,
                'cliente_nombre' => $cliente->nombre,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔐 Registrar escaneo de QR (auditoría)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logScan(Request $request)
    {
        try {
            $validated = $request->validate([
                'qr_code' => 'required|string',
            ]);

            $log = QRScanLog::registrar(
                $validated['qr_code'],
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Escaneo registrado',
                'log_id' => $log->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar escaneo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 Obtener historial de escaneos de un QR
     * 
     * @param string $qrCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanHistory($qrCode)
    {
        try {
            $logs = QRScanLog::where('qr_code', $qrCode)
                ->with('usuario:id,name,email')
                ->orderBy('scanned_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'logs' => $logs,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📈 Estadísticas de escaneos por mascota
     * 
     * @param int $mascotaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanStats($mascotaId)
    {
        try {
            $mascota = Mascota::findOrFail($mascotaId);

            $stats = [
                'total_scans' => QRScanLog::where('qr_code', $mascota->qr_code)->count(),
                'scans_last_7_days' => QRScanLog::where('qr_code', $mascota->qr_code)
                    ->where('scanned_at', '>=', now()->subDays(7))
                    ->count(),
                'scans_last_30_days' => QRScanLog::where('qr_code', $mascota->qr_code)
                    ->where('scanned_at', '>=', now()->subDays(30))
                    ->count(),
                'unique_scanners' => QRScanLog::where('qr_code', $mascota->qr_code)
                    ->distinct('scanned_by')
                    ->count('scanned_by'),
                'last_scan' => QRScanLog::where('qr_code', $mascota->qr_code)
                    ->with('usuario:id,name,email')
                    ->latest('scanned_at')
                    ->first(),
            ];

            return response()->json([
                'success' => true,
                'mascota' => [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'qr_code' => $mascota->qr_code,
                ],
                'stats' => $stats,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
