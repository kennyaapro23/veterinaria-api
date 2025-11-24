<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Mascota;
use App\Models\Servicio;
use App\Models\Notificacion;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Cita::with(['cliente', 'mascota', 'veterinario', 'servicios']);

        // Si el usuario es cliente, asegurar que tenga Cliente (auto-create si falta)
        if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
            Cliente::create([
                'user_id' => $user->id,
                'nombre' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'es_walk_in' => false,
            ]);
            $user->load('cliente');
        }

        // Filtro por ROL
        if ($user->tipo_usuario === 'cliente') {
            // CLIENTE: Solo ve sus propias citas
            $cliente = $user->cliente;
            $query->where('cliente_id', $cliente->id);
        } elseif ($user->tipo_usuario === 'veterinario') {
            // VETERINARIO: Solo ve sus propias citas (a menos que se filtre por otro)
            $veterinario = $user->veterinario;
            if ($veterinario && !$request->has('veterinario_id')) {
                $query->where('veterinario_id', $veterinario->id);
            }
        }
        // RECEPCIÓN: Ve todas las citas sin filtro

        // Filtro por veterinario (solo para recepción o si veterinario consulta otro)
        if ($request->has('veterinario_id')) {
            $query->where('veterinario_id', $request->veterinario_id);
        }

        // Filtro por cliente (solo para recepción/veterinario)
        if ($request->has('cliente_id') && $user->tipo_usuario !== 'cliente') {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtro por mascota
        if ($request->has('mascota_id')) {
            $query->where('mascota_id', $request->mascota_id);
        }

        // Filtro por fecha exacta
        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda por nombre de mascota
        if ($request->has('nombre_mascota')) {
            $query->whereHas('mascota', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre_mascota . '%');
            });
        }

        // Búsqueda por nombre de cliente
        if ($request->has('nombre_cliente')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre_cliente . '%');
            });
        }

        // Búsqueda por nombre de veterinario
        if ($request->has('nombre_veterinario')) {
            $query->whereHas('veterinario', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->nombre_veterinario . '%');
            });
        }

        // Búsqueda general (motivo, notas)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'like', "%{$search}%")
                  ->orWhere('notas', 'like', "%{$search}%");
            });
        }

        $citas = $query->orderBy('fecha', 'desc')->paginate(15);

        return response()->json($citas);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Normalize `servicios`: accept one id (int/string), an array of ids, or null.
        // If a single id is sent (e.g. servicios: 3) we convert it to [3] so validation
        // and later logic work uniformly.
        if ($request->has('servicios') && !is_array($request->input('servicios'))) {
            $request->merge(['servicios' => [$request->input('servicios')]]);
        }

        // Si es CLIENTE, auto-asignar su cliente_id (no puede crear citas para otros)
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            
            if (!$cliente) {
                // Crear perfil Cliente automáticamente si falta
                $cliente = Cliente::create([
                    'user_id' => $user->id,
                    'nombre' => $user->name ?? 'Cliente',
                    'email' => $user->email ?? null,
                    'es_walk_in' => false,
                ]);
                $user->load('cliente');
            }
            
            // Validación para CLIENTE (no requiere cliente_id, se asigna automáticamente)
            // `servicios` puede ser null, o una lista de ids; ya normalizamos un id simple arriba.
            $validated = $request->validate([
                'mascota_id' => 'required|exists:mascotas,id',
                'veterinario_id' => 'required|exists:veterinarios,id',
                'fecha' => 'required|date|after:now',
                'motivo' => 'nullable|string|max:255',
                'notas' => 'nullable|string',
                'servicios' => 'nullable|array',
                'servicios.*' => 'exists:servicios,id',
            ]);
            
            // Auto-asignar cliente_id
            $validated['cliente_id'] = $cliente->id;
            
        } else {
            // Veterinario/Recepción pueden especificar cliente_id
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'mascota_id' => 'required|exists:mascotas,id',
                'veterinario_id' => 'required|exists:veterinarios,id',
                'fecha' => 'required|date|after:now',
                'motivo' => 'nullable|string|max:255',
                'notas' => 'nullable|string',
                'servicios' => 'nullable|array',
                'servicios.*' => 'exists:servicios,id',
            ]);
        }

        // 1. Validar que mascota pertenezca al cliente
        $mascota = Mascota::findOrFail($validated['mascota_id']);
        if ($mascota->cliente_id !== $validated['cliente_id']) {
            return response()->json([
                'error' => 'La mascota no pertenece al cliente especificado'
            ], 422);
        }

        // 2. Lugar/direccion removed: all appointments are in-clinic -> no domicilio validation

        // 3. Calcular duración total por servicios
        // Si no se enviaron servicios, usamos una duración por defecto (30 minutos).
        $defaultDuration = 30;
        $duracion_total = $defaultDuration;
        $servicios = collect();
        if (!empty($validated['servicios'])) {
            $servicios = Servicio::whereIn('id', $validated['servicios'])->get();
            $duracion_total = $servicios->sum('duracion_minutos');
        }

        // 4. Verificar disponibilidad del veterinario (evitar solapamiento)
        $fecha = Carbon::parse($validated['fecha']);
        $fecha_fin = $fecha->copy()->addMinutes($duracion_total);

        $conflicto = Cita::where('veterinario_id', $validated['veterinario_id'])
            ->whereNotIn('estado', ['cancelada'])
            ->where(function ($query) use ($fecha, $fecha_fin) {
                // Verificar si hay solapamiento
                $query->where(function ($q) use ($fecha, $fecha_fin) {
                    // Nueva cita empieza durante una cita existente
                    $q->where('fecha', '<=', $fecha)
                      ->whereRaw('DATE_ADD(fecha, INTERVAL duracion_minutos MINUTE) > ?', [$fecha]);
                })
                ->orWhere(function ($q) use ($fecha, $fecha_fin) {
                    // Nueva cita termina durante una cita existente
                    $q->where('fecha', '<', $fecha_fin)
                      ->whereRaw('DATE_ADD(fecha, INTERVAL duracion_minutos MINUTE) > ?', [$fecha_fin]);
                })
                ->orWhere(function ($q) use ($fecha, $fecha_fin) {
                    // Nueva cita envuelve completamente una cita existente
                    $q->where('fecha', '>=', $fecha)
                      ->where('fecha', '<', $fecha_fin);
                });
            })
            ->exists();

        if ($conflicto) {
            return response()->json([
                'error' => 'El veterinario no está disponible en ese horario. Existe un conflicto de agenda.'
            ], 409);
        }

        // 5. Crear la cita
        DB::beginTransaction();
        try {
            $cita = Cita::create([
                'cliente_id' => $validated['cliente_id'],
                'mascota_id' => $validated['mascota_id'],
                'veterinario_id' => $validated['veterinario_id'],
                'fecha' => $validated['fecha'],
                'duracion_minutos' => $duracion_total,
                'estado' => 'pendiente',
                'motivo' => $validated['motivo'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'created_by' => auth()->id(),
                // lugar/direccion removed: default behavior is in-clinic
            ]);

            // 6. Adjuntar servicios con precios actuales (trazabilidad histórica)
            if ($servicios->isNotEmpty()) {
                foreach ($servicios as $servicio) {
                    $cita->servicios()->attach($servicio->id, [
                        'cantidad' => 1,
                        'precio_unitario' => $servicio->precio,
                        'notas' => null,
                    ]);
                }
            }

            // 7. Crear notificación en base de datos
            if ($cita->cliente->user_id) {
                Notificacion::create([
                    'user_id' => $cita->cliente->user_id,
                    'tipo' => 'cita_creada',
                    'titulo' => 'Nueva Cita Confirmada',
                    'cuerpo' => "Tu cita para {$mascota->nombre} ha sido confirmada para el {$fecha->format('d/m/Y H:i')}",
                    'leida' => false,
                    'meta' => json_encode(['cita_id' => $cita->id]),
                    'sent_via' => 'push',
                ]);

                // 8. Enviar notificación push a todos los dispositivos del cliente
                $cliente = $cita->cliente;
                if ($cliente->user) {
                    sendPushToUser(
                        $cliente->user,
                        '🗓️ Nueva Cita Confirmada',
                        "Tu cita para {$mascota->nombre} está programada para el {$fecha->format('d/m/Y')} a las {$fecha->format('H:i')}",
                        [
                            'tipo' => 'cita_creada',
                            'cita_id' => $cita->id,
                            'fecha' => $fecha->toISOString(),
                        ]
                    );
                }
            }

            // 9. Registrar auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_cita',
                'tabla' => 'citas',
                'registro_id' => $cita->id,
                'cambios' => json_encode($cita->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cita creada exitosamente',
                'cita' => $cita->load(['cliente', 'mascota', 'veterinario', 'servicios'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $cita = Cita::with(['cliente', 'mascota', 'veterinario', 'servicios', 'historialMedicos'])
            ->findOrFail($id);

        // Asegurar cliente para usuarios tipo 'cliente'
        if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
            Cliente::create([
                'user_id' => $user->id,
                'nombre' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'es_walk_in' => false,
            ]);
            $user->load('cliente');
        }

        // Verificar permisos por ROL
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            if (!$cliente || $cita->cliente_id !== $cliente->id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver esta cita'
                ], 403);
            }
        } elseif ($user->tipo_usuario === 'veterinario') {
            $veterinario = $user->veterinario;
            if (!$veterinario || $cita->veterinario_id !== $veterinario->id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver esta cita'
                ], 403);
            }
        }
        // RECEPCIÓN puede ver cualquier cita

        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
            Cliente::create([
                'user_id' => $user->id,
                'nombre' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'es_walk_in' => false,
            ]);
            $user->load('cliente');
        }
        $cita = Cita::findOrFail($id);
        
        // CLIENTE: Solo puede cancelar su propia cita
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            if (!$cliente || $cita->cliente_id !== $cliente->id) {
                return response()->json([
                    'error' => 'No tienes permiso para modificar esta cita'
                ], 403);
            }
            
            // Cliente solo puede cancelar
            if ($request->has('estado') && $request->estado !== 'cancelada') {
                return response()->json([
                    'error' => 'Solo puedes cancelar tus citas'
                ], 403);
            }
        }

        $validated = $request->validate([
            'fecha' => 'nullable|date|after:now',
            'estado' => 'nullable|in:pendiente,confirmado,atendida,cancelada,reprogramada',
            'notas' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Reprogramar cita
            if ($request->has('fecha')) {
                $nueva_fecha = Carbon::parse($validated['fecha']);
                $fecha_fin = $nueva_fecha->copy()->addMinutes($cita->duracion_minutos);

                // Validar disponibilidad (excluir la cita actual)
                $conflicto = Cita::where('veterinario_id', $cita->veterinario_id)
                    ->where('id', '!=', $cita->id)
                    ->whereNotIn('estado', ['cancelada'])
                    ->where(function ($query) use ($nueva_fecha, $fecha_fin) {
                        $query->where(function ($q) use ($nueva_fecha, $fecha_fin) {
                            $q->where('fecha', '<=', $nueva_fecha)
                              ->whereRaw('DATE_ADD(fecha, INTERVAL duracion_minutos MINUTE) > ?', [$nueva_fecha]);
                        })
                        ->orWhere(function ($q) use ($nueva_fecha, $fecha_fin) {
                            $q->where('fecha', '<', $fecha_fin)
                              ->whereRaw('DATE_ADD(fecha, INTERVAL duracion_minutos MINUTE) > ?', [$fecha_fin]);
                        })
                        ->orWhere(function ($q) use ($nueva_fecha, $fecha_fin) {
                            $q->where('fecha', '>=', $nueva_fecha)
                              ->where('fecha', '<', $fecha_fin);
                        });
                    })
                    ->exists();

                if ($conflicto) {
                    return response()->json([
                        'error' => 'El veterinario no está disponible en ese horario'
                    ], 409);
                }

                $cita->fecha = $validated['fecha'];
                $cita->estado = 'reprogramada';

                // Notificar reprogramación
                if ($cita->cliente->user_id) {
                    Notificacion::create([
                        'user_id' => $cita->cliente->user_id,
                        'tipo' => 'cita_modificada',
                        'titulo' => 'Cita Reprogramada',
                        'cuerpo' => "Tu cita ha sido reprogramada para el {$nueva_fecha->format('d/m/Y H:i')}",
                        'meta' => json_encode(['cita_id' => $cita->id]),
                        'sent_via' => 'push',
                    ]);
                    
                    // Enviar push notification
                    $mascota = $cita->mascota;
                    if ($cita->cliente->user) {
                        sendPushToUser(
                            $cita->cliente->user,
                            '🔄 Cita Reprogramada',
                            "Tu cita para {$mascota->nombre} fue reprogramada para el {$nueva_fecha->format('d/m/Y')} a las {$nueva_fecha->format('H:i')}",
                            [
                                'tipo' => 'cita_modificada',
                                'cita_id' => $cita->id,
                                'fecha' => $nueva_fecha->toISOString(),
                            ]
                        );
                    }
                }
            }

            // Cambiar estado (incluyendo cancelación)
            if ($request->has('estado')) {
                $cita->estado = $validated['estado'];

                if ($validated['estado'] === 'cancelada') {
                    if ($cita->cliente->user_id) {
                        Notificacion::create([
                            'user_id' => $cita->cliente->user_id,
                            'tipo' => 'cita_cancelada',
                            'titulo' => 'Cita Cancelada',
                            'cuerpo' => 'Tu cita ha sido cancelada',
                            'meta' => json_encode(['cita_id' => $cita->id]),
                            'sent_via' => 'push',
                        ]);
                        
                        // Enviar push notification
                        $mascota = $cita->mascota;
                        if ($cita->cliente->user) {
                            sendPushToUser(
                                $cita->cliente->user,
                                '❌ Cita Cancelada',
                                "Tu cita para {$mascota->nombre} ha sido cancelada",
                                [
                                    'tipo' => 'cita_cancelada',
                                    'cita_id' => $cita->id,
                                ]
                            );
                        }
                    }
                }
            }

            if ($request->has('notas')) {
                $cita->notas = $validated['notas'];
            }

            $cita->save();

            // Registrar auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => $request->has('estado') && $validated['estado'] === 'cancelada' ? 'cancelar_cita' : 'actualizar_cita',
                'tabla' => 'citas',
                'registro_id' => $cita->id,
                'cambios' => json_encode($cita->getChanges()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cita actualizada exitosamente',
                'cita' => $cita->load(['cliente', 'mascota', 'veterinario', 'servicios'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
            Cliente::create([
                'user_id' => $user->id,
                'nombre' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'es_walk_in' => false,
            ]);
            $user->load('cliente');
        }
        $cita = Cita::findOrFail($id);

        // Verificar permisos por ROL
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            if (!$cliente || $cita->cliente_id !== $cliente->id) {
                return response()->json([
                    'error' => 'No tienes permiso para cancelar esta cita'
                ], 403);
            }
        } elseif ($user->tipo_usuario === 'veterinario') {
            return response()->json([
                'error' => 'Los veterinarios no pueden eliminar citas, solo cambiar su estado'
            ], 403);
        }
        // RECEPCIÓN puede cancelar cualquier cita

        // Solo permitir cancelación, no eliminación física
        $cita->estado = 'cancelada';
        $cita->save();

        // Notificar
        if ($cita->cliente->user_id) {
            Notificacion::create([
                'user_id' => $cita->cliente->user_id,
                'tipo' => 'cita_cancelada',
                'titulo' => 'Cita Cancelada',
                'cuerpo' => 'Tu cita ha sido cancelada',
                'meta' => json_encode(['cita_id' => $cita->id]),
                'sent_via' => 'push',
            ]);
        }

        // Auditoría
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'accion' => 'cancelar_cita',
            'tabla' => 'citas',
            'registro_id' => $cita->id,
            'cambios' => json_encode(['estado' => 'cancelada']),
        ]);

        return response()->json([
            'message' => 'Cita cancelada exitosamente'
        ]);
    }
}
