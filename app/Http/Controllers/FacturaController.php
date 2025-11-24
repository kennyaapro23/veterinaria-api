<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    /**
     * Listar facturas
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Factura::with(['cita.mascota', 'cita.cliente']);

        // Filtrar según rol del usuario
        if ($user->hasRole('cliente')) {
            $query->whereHas('cita.cliente', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->hasRole('veterinario')) {
            $query->whereHas('cita', function ($q) use ($user) {
                $q->where('veterinario_id', $user->veterinario->id);
            });
        }
        // recepcion y admin ven todas

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        if ($request->has('numero_factura')) {
            $query->where('numero_factura', 'like', '%' . $request->numero_factura . '%');
        }

        // Filtrar por nombre de cliente (puede venir como cliente asociado a la factura o a la cita)
        if ($request->has('cliente_nombre')) {
            $clienteNombre = $request->cliente_nombre;
            $query->where(function ($q) use ($clienteNombre) {
                $q->whereHas('cliente', function ($q2) use ($clienteNombre) {
                    $q2->where('nombre', 'like', '%' . $clienteNombre . '%');
                })->orWhereHas('cita.cliente', function ($q3) use ($clienteNombre) {
                    $q3->where('nombre', 'like', '%' . $clienteNombre . '%');
                });
            });
        }

        // Filtrar por nombre de mascota (puede venir por la cita o por historiales asociados)
        if ($request->has('mascota_nombre')) {
            $mascotaNombre = $request->mascota_nombre;
            $query->where(function ($q) use ($mascotaNombre) {
                $q->whereHas('cita.mascota', function ($q2) use ($mascotaNombre) {
                    $q2->where('nombre', 'like', '%' . $mascotaNombre . '%');
                })->orWhereHas('historiales', function ($q3) use ($mascotaNombre) {
                    $q3->whereHas('mascota', function ($q4) use ($mascotaNombre) {
                        $q4->where('nombre', 'like', '%' . $mascotaNombre . '%');
                    });
                });
            });
        }

        $facturas = $query->latest('fecha_emision')->paginate(20);

        return response()->json($facturas);
    }

    /**
     * Crear factura desde una cita
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cita_id' => 'required|exists:citas,id',
            'numero_factura' => 'required|string|max:50|unique:facturas,numero_factura',
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,transferencia,otro',
            'notas' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $cita = Cita::with('servicios')->findOrFail($validated['cita_id']);

            // Verificar que no exista factura para esta cita
            if ($cita->factura) {
                return response()->json([
                    'error' => 'Esta cita ya tiene una factura asociada'
                ], 422);
            }

            // Calcular subtotal desde la tabla pivot cita_servicio
            $subtotal = DB::table('cita_servicio')
                ->where('cita_id', $cita->id)
                ->sum('precio_momento');

            // Calcular impuestos (ejemplo: 16% IVA)
            $impuestos = round($subtotal * 0.16, 2);
            $total = $subtotal + $impuestos;

            // Construir detalles para la factura (servicios, mascota, subtotal por línea)
            $detalles = [
                'origen' => 'cita',
                'cita_id' => $cita->id,
                'mascota' => $cita->mascota ? [
                    'id' => $cita->mascota->id,
                    'nombre' => $cita->mascota->nombre,
                ] : null,
                'servicios' => $cita->servicios->map(function ($s) {
                    $cantidad = $s->pivot->cantidad ?? 1;
                    $precio = $s->pivot->precio_unitario ?? ($s->precio ?? 0);
                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre ?? null,
                        'cantidad' => $cantidad,
                        'precio_unitario' => (float) $precio,
                        'subtotal' => round($cantidad * $precio, 2),
                    ];
                })->values()->toArray(),
                'subtotal' => (float) $subtotal,
                'impuestos' => (float) $impuestos,
                'total' => (float) $total,
            ];

            $factura = Factura::create([
                'cita_id' => $cita->id,
                'numero_factura' => $validated['numero_factura'],
                'fecha_emision' => now(),
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                'estado' => 'pendiente',
                'metodo_pago' => $validated['metodo_pago'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'detalles' => $detalles,
            ]);

            // Marcar la cita como atendida si corresponde (evita dejarla en pendiente)
            if ($cita->estado !== 'atendida') {
                $cita->update(['estado' => 'atendida']);
            }

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_factura',
                'tabla' => 'facturas',
                'registro_id' => $factura->id,
                'cambios' => json_encode($factura->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Factura creada exitosamente',
                'factura' => $factura->load('cita.servicios')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear factura desde historiales médicos
     */
    public function storeFromHistoriales(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'historial_ids' => 'required|array|min:1',
            'historial_ids.*' => 'required|exists:historial_medicos,id',
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,transferencia,otro',
            'notas' => 'nullable|string',
            'tasa_impuesto' => 'nullable|numeric|min:0|max:100', // Porcentaje de impuesto (ej: 16)
        ]);

        DB::beginTransaction();
        try {
            // Verificar que todos los historiales sean del mismo cliente
            $historiales = \App\Models\HistorialMedico::with(['servicios', 'mascota.cliente'])
                ->whereIn('id', $validated['historial_ids'])
                ->get();

            foreach ($historiales as $historial) {
                if ($historial->mascota->cliente_id != $validated['cliente_id']) {
                    return response()->json([
                        'error' => "El historial #{$historial->id} no pertenece al cliente especificado"
                    ], 422);
                }

                if ($historial->facturado) {
                    return response()->json([
                        'error' => "El historial #{$historial->id} ya ha sido facturado"
                    ], 422);
                }
            }

            // Generar número de factura
            $year = date('Y');
            $lastFactura = \App\Models\Factura::whereYear('fecha_emision', $year)
                ->orderBy('numero_factura', 'desc')
                ->first();

            if ($lastFactura) {
                preg_match('/(\d+)$/', $lastFactura->numero_factura, $matches);
                $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            } else {
                $nextNumber = 1;
            }

            $numeroFactura = sprintf('FAC-%s-%05d', $year, $nextNumber);

            // Calcular subtotal sumando total_servicios de cada historial
            $subtotal = 0;
            $historialSubtotales = [];

            foreach ($historiales as $historial) {
                $totalHistorial = $historial->total_servicios;
                $subtotal += $totalHistorial;
                $historialSubtotales[$historial->id] = $totalHistorial;
            }

            // Calcular impuestos (default 16% IVA si no se especifica)
            $tasaImpuesto = $validated['tasa_impuesto'] ?? 16;
            $impuestos = round($subtotal * ($tasaImpuesto / 100), 2);
            $total = $subtotal + $impuestos;

            // Crear factura
            $factura = \App\Models\Factura::create([
                'cliente_id' => $validated['cliente_id'],
                'numero_factura' => $numeroFactura,
                'fecha_emision' => now(),
                'subtotal' => $subtotal,
                'impuestos' => $impuestos,
                'total' => $total,
                // Por defecto al facturar desde historiales, consideramos que ya se paga
                'estado' => 'pagado',
                'fecha_pago' => now(),
                'metodo_pago' => $validated['metodo_pago'] ?? null,
                'notas' => $validated['notas'] ?? null,
            ]);

            // Construir detalles agrupando cada historial y sus servicios
            $detalles = [
                'origen' => 'historiales',
                'historiales' => [],
                'subtotal' => (float) $subtotal,
                'impuestos' => (float) $impuestos,
                'total' => (float) $total,
            ];

            foreach ($historiales as $historial) {
                $servs = $historial->servicios->map(function ($s) {
                    $cantidad = $s->pivot->cantidad ?? 1;
                    $precio = $s->pivot->precio_unitario ?? ($s->precio ?? 0);
                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre ?? null,
                        'cantidad' => $cantidad,
                        'precio_unitario' => (float) $precio,
                        'subtotal' => round($cantidad * $precio, 2),
                    ];
                })->values()->toArray();

                $detalles['historiales'][] = [
                    'historial_id' => $historial->id,
                    'cita_id' => $historial->cita_id,
                    'id_cita' => $historial->cita_id,
                    'fecha' => $historial->fecha ? $historial->fecha->toDateString() : null,
                    'mascota' => $historial->mascota ? [
                        'id' => $historial->mascota->id,
                        'nombre' => $historial->mascota->nombre,
                    ] : null,
                    'servicios' => $servs,
                    'subtotal' => (float) ($historialSubtotales[$historial->id] ?? 0),
                ];
            }

            // Guardar detalles JSON en la factura
            $factura->detalles = $detalles;
            $factura->save();

            // Si la factura fue creada a partir de un único historial, enlazar la factura a la cita asociada
            // así la factura queda referenciada por cita (útil para vistas y permisos)
            if ($historiales->count() === 1) {
                $single = $historiales->first();
                if ($single && $single->cita_id) {
                    $factura->cita_id = $single->cita_id;
                    // agregar la referencia de cita al detalle principal también
                    $det = $factura->detalles;
                    if (is_array($det)) {
                        $det['cita_id'] = $single->cita_id;
                        $det['id_cita'] = $single->cita_id;
                        $factura->detalles = $det;
                    }
                    $factura->save();
                }
            }

            // Adjuntar historiales a la factura con sus subtotales
            $pivotData = [];
            foreach ($historialSubtotales as $historialId => $subtotalHistorial) {
                $pivotData[$historialId] = [
                    'subtotal' => $subtotalHistorial,
                ];
            }

            $factura->historiales()->attach($pivotData);

            // Marcar historiales como facturados
            \App\Models\HistorialMedico::whereIn('id', $validated['historial_ids'])
                ->update([
                    'facturado' => true,
                    'factura_id' => $factura->id,
                ]);

            // Actualizar el estado de las citas relacionadas: si todas sus historiales están facturados,
            // marcar la cita como 'atendida'. Esto evita dejar citas en 'pendiente' después de facturar.
            $citaIds = $historiales->pluck('cita_id')->unique()->filter();
            foreach ($citaIds as $citaId) {
                if (!$citaId) continue;

                $tienePendientes = \App\Models\HistorialMedico::where('cita_id', $citaId)
                    ->where(function ($q) {
                        $q->whereNull('facturado')->orWhere('facturado', false);
                    })->exists();

                if (!$tienePendientes) {
                    \App\Models\Cita::where('id', $citaId)->update(['estado' => 'atendida']);
                }
            }

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_factura_historiales',
                'tabla' => 'facturas',
                'registro_id' => $factura->id,
                'cambios' => json_encode([
                    'factura' => $factura->toArray(),
                    'historial_ids' => $validated['historial_ids'],
                ]),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Factura creada exitosamente desde historiales',
                'factura' => $factura->load(['historiales.servicios', 'cliente']),
                'total_historiales' => count($validated['historial_ids']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver factura
     */
    public function show($id)
    {
        $factura = Factura::with([
            'cita.mascota',
            'cita.cliente.user',
            'cita.veterinario.user',
            'cita.servicios',
            'historiales.servicios',
            'cliente'
        ])->findOrFail($id);

        // Verificar permisos
        $user = auth()->user();
        if ($user->hasRole('cliente')) {
            if ($factura->cita->cliente->user_id !== $user->id) {
                return response()->json([
                    'error' => 'No autorizado para ver esta factura'
                ], 403);
            }
        } elseif ($user->hasRole('veterinario')) {
            if ($factura->cita->veterinario_id !== $user->veterinario->id) {
                return response()->json([
                    'error' => 'No autorizado para ver esta factura'
                ], 403);
            }
        }

        return response()->json($factura);
    }

    /**
     * Actualizar factura (principalmente estado de pago)
     */
    public function update(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'sometimes|required|in:pendiente,pagado,anulado',
            'metodo_pago' => 'nullable|in:efectivo,tarjeta,transferencia,otro',
            'fecha_pago' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Si se marca como pagado, registrar fecha de pago
            if (isset($validated['estado']) && $validated['estado'] === 'pagado' && !$factura->fecha_pago) {
                $validated['fecha_pago'] = now();
            }

            $factura->update($validated);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'actualizar_factura',
                'tabla' => 'facturas',
                'registro_id' => $factura->id,
                'cambios' => json_encode($factura->getChanges()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Factura actualizada exitosamente',
                'factura' => $factura
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anular factura
     */
    public function destroy($id)
    {
        $factura = Factura::findOrFail($id);

        // Solo se pueden anular facturas pendientes
        if ($factura->estado === 'pagado') {
            return response()->json([
                'error' => 'No se puede eliminar una factura pagada. Puede anularla cambiando su estado.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'eliminar_factura',
                'tabla' => 'facturas',
                'registro_id' => $factura->id,
                'cambios' => json_encode($factura->toArray()),
            ]);

            $factura->delete();

            DB::commit();

            return response()->json([
                'message' => 'Factura eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar número de factura automático
     */
    public function generateNumeroFactura()
    {
        $year = date('Y');
        $lastFactura = Factura::whereYear('fecha_emision', $year)
            ->orderBy('numero_factura', 'desc')
            ->first();

        if ($lastFactura) {
            // Extraer el número secuencial
            preg_match('/(\d+)$/', $lastFactura->numero_factura, $matches);
            $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            $nextNumber = 1;
        }

        $numeroFactura = sprintf('FAC-%s-%05d', $year, $nextNumber);

        return response()->json([
            'numero_factura' => $numeroFactura
        ]);
    }

    /**
     * Estadísticas de facturación
     */
    public function getEstadisticas(Request $request)
    {
        $query = Factura::query();

        // Filtro de fechas
        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        $stats = [
            'total_facturado' => $query->clone()->where('estado', 'pagado')->sum('total'),
            'total_pendiente' => $query->clone()->where('estado', 'pendiente')->sum('total'),
            'total_anulado' => $query->clone()->where('estado', 'anulado')->sum('total'),
            'cantidad_facturas' => $query->clone()->count(),
            'cantidad_pagadas' => $query->clone()->where('estado', 'pagado')->count(),
            'cantidad_pendientes' => $query->clone()->where('estado', 'pendiente')->count(),
            'promedio_factura' => $query->clone()->where('estado', 'pagado')->avg('total'),
        ];

        return response()->json($stats);
    }
}
