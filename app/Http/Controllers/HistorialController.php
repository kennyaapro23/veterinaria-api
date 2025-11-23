<?php

namespace App\Http\Controllers;

use App\Models\HistorialMedico;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class HistorialController extends Controller
{
    /**
     * Listar historial médico con filtros
     */
    public function index(Request $request)
    {
        $user = auth()->user();
    $query = HistorialMedico::with(['mascota.cliente', 'cita', 'realizadoPor', 'servicios']);

        // Filtro por ROL
        if ($user->tipo_usuario === 'cliente') {
            // CLIENTE: Solo ve historiales de sus mascotas
            $cliente = $user->cliente;
            if (!$cliente) {
                return response()->json(['data' => []]);
            }
            $query->whereHas('mascota', function ($q) use ($cliente) {
                $q->where('cliente_id', $cliente->id);
            });
        }
        // VETERINARIO y RECEPCIÓN: Ven todos los historiales

        // Filtro por mascota
        if ($request->has('mascota_id')) {
            $query->where('mascota_id', $request->mascota_id);
        }

        // Filtro por fecha inicial
        if ($request->has('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }

        // Filtro por fecha final
        if ($request->has('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por veterinario
        if ($request->has('veterinario_id')) {
            $query->where('realizado_por', $request->veterinario_id);
        }

        // Filtro por tipo
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por estado de facturación
        if ($request->has('facturado')) {
            $facturado = filter_var($request->facturado, FILTER_VALIDATE_BOOLEAN);
            $query->where('facturado', $facturado);
        }

        // Búsqueda por nombre de mascota
        if ($request->has('nombre_mascota')) {
            $query->whereHas('mascota', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre_mascota . '%');
            });
        }

        // Búsqueda por nombre de dueño/cliente
        if ($request->has('nombre_cliente')) {
            $query->whereHas('mascota.cliente', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre_cliente . '%');
            });
        }

        // Búsqueda general (diagnóstico, tratamiento, observaciones)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('diagnostico', 'like', "%{$search}%")
                  ->orWhere('tratamiento', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        $historial = $query->orderBy('fecha', 'desc')->paginate(20);

        return response()->json($historial);
    }

    /**
     * Crear entrada de historial médico (SOLO VETERINARIO)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Solo VETERINARIO puede crear historiales médicos
        if ($user->tipo_usuario !== 'veterinario') {
            return response()->json([
                'error' => 'Solo veterinarios pueden crear registros de historial médico'
            ], 403);
        }

        $validated = $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'cita_id' => 'nullable|exists:citas,id',
            'fecha' => 'nullable|date',
            'tipo' => 'required|in:consulta,vacuna,procedimiento,control,otro',
            // diagnostico puede ser un string simple o un array de strings
            'diagnostico' => 'nullable',
            'diagnostico.*' => 'string',
            'tratamiento' => 'nullable|string',
            'observaciones' => 'nullable|string',
            // archivos removed: file upload not supported anymore
            'servicios' => 'nullable|array',
            'servicios.*.servicio_id' => 'required|exists:servicios,id',
            'servicios.*.cantidad' => 'nullable|integer|min:1',
            'servicios.*.precio_unitario' => 'nullable|numeric|min:0',
            'servicios.*.notas' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Obtener el veterinario asociado al usuario
            $veterinario = auth()->user()->veterinario;

            if (!$veterinario) {
                return response()->json([
                    'error' => 'Usuario no tiene perfil de veterinario asociado'
                ], 403);
            }

            $historial = HistorialMedico::create([
                'mascota_id' => $validated['mascota_id'],
                'cita_id' => $validated['cita_id'] ?? null,
                'fecha' => $validated['fecha'] ?? now(),
                'tipo' => $validated['tipo'],
                // Normalizar: si viene string, convertir a array con un elemento
                'diagnostico' => isset($validated['diagnostico'])
                    ? (is_array($validated['diagnostico']) ? $validated['diagnostico'] : [ (string) $validated['diagnostico'] ])
                    : null,
                'tratamiento' => $validated['tratamiento'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'realizado_por' => $veterinario->id,
            ]);
            
            // Si se proporcionó cita_id, validar que la cita corresponda a la mascota indicada
            if (!empty($validated['cita_id'])) {
                $cita = \App\Models\Cita::find($validated['cita_id']);
                if (!$cita) {
                    DB::rollBack();
                    return response()->json(['error' => 'Cita especificada no encontrada'], 422);
                }
                if ($cita->mascota_id != $validated['mascota_id']) {
                    DB::rollBack();
                    return response()->json(['error' => 'La cita especificada no corresponde a la mascota indicada'], 422);
                }
            }

            // Adjuntar servicios al historial
            if (!empty($validated['servicios'])) {
                $serviciosData = [];
                
                foreach ($validated['servicios'] as $servicioData) {
                    // Si no se especifica precio, usar el precio del servicio
                    $servicio = \App\Models\Servicio::findOrFail($servicioData['servicio_id']);
                    
                    $serviciosData[$servicioData['servicio_id']] = [
                        'cantidad' => $servicioData['cantidad'] ?? 1,
                        'precio_unitario' => $servicioData['precio_unitario'] ?? $servicio->precio,
                        'notas' => $servicioData['notas'] ?? null,
                    ];
                }
                
                $historial->servicios()->attach($serviciosData);
            }

            // Si se proporcionó cita_id, marcar la cita como atendida
            if (!empty($validated['cita_id'])) {
                $citaToMark = \App\Models\Cita::find($validated['cita_id']);
                if ($citaToMark) {
                    // Actualizar solo si el estado no es ya 'atendida'
                    if ($citaToMark->estado !== 'atendida') {
                        $citaToMark->update(['estado' => 'atendida']);
                    }
                }
            }

            // archivos handling removed: uploads are not supported

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_historial_medico',
                'tabla' => 'historial_medicos',
                'registro_id' => $historial->id,
                'cambios' => json_encode($historial->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Historial médico creado exitosamente',
                'historial' => $historial->load(['mascota', 'cita', 'realizadoPor', 'servicios']),
                'total_servicios' => $historial->total_servicios
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear historial médico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de entrada de historial
     */
    public function show($id)
    {
        $user = auth()->user();
        $historial = HistorialMedico::with([
            'mascota.cliente',
            'cita',
            'realizadoPor',
            'servicios'
        ])->findOrFail($id);

        // Verificar permisos por ROL
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            if (!$cliente || $historial->mascota->cliente_id !== $cliente->id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver este historial médico'
                ], 403);
            }
        }
        // VETERINARIO y RECEPCIÓN pueden ver cualquier historial

        return response()->json([
            'historial' => $historial,
            'total_servicios' => $historial->total_servicios
        ]);
    }

    /**
     * Adjuntar archivos a un historial existente (SOLO VETERINARIO)
     */
    public function attachFiles(Request $request, $id)
    {
        // attachFiles removed: file upload endpoint disabled
        return response()->json([
            'error' => 'La funcionalidad de subir archivos fue deshabilitada'
        ], 410);
    }
}
