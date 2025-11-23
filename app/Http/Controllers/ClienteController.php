<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    /**
     * Listar clientes (SOLO RECEPCIÓN)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede listar clientes
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para ver la lista de clientes'
            ], 403);
        }
        
        $query = Cliente::with(['user', 'mascotas']);

        // Búsqueda por nombre o email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($clientes);
    }

    /**
     * Crear cliente (SOLO RECEPCIÓN)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede crear clientes
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para crear clientes'
            ], 403);
        }
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:clientes,user_id',
            'nombre' => 'required|string|max:150',
            'telefono' => 'required|string|max:20', // ✅ Obligatorio para walk-ins
            'email' => 'nullable|email|unique:clientes,email', // ✅ Opcional para walk-ins
            'documento_tipo' => 'nullable|string|max:50',
            'documento_num' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'es_walk_in' => 'nullable|boolean', // ✅ Flag para identificar walk-ins
        ]);

        DB::beginTransaction();
        try {
            $cliente = Cliente::create($validated);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_cliente',
                'tabla' => 'clientes',
                'registro_id' => $cliente->id,
                'cambios' => json_encode($cliente->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cliente creado exitosamente',
                'cliente' => $cliente
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver cliente (SOLO RECEPCIÓN puede ver cualquiera, CLIENTE solo su perfil)
     */
    public function show($id)
    {
        $user = auth()->user();
        
        // CLIENTE: Solo puede ver su propio perfil
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            if (!$cliente || $cliente->id != $id) {
                return response()->json([
                    'error' => 'No tienes permiso para ver este perfil'
                ], 403);
            }
        } elseif ($user->tipo_usuario !== 'recepcion') {
            // VETERINARIO: No puede ver perfiles de clientes
            return response()->json([
                'error' => 'No tienes permiso para ver perfiles de clientes'
            ], 403);
        }
        
        $cliente = Cliente::with([
            'user',
            'mascotas',
            'citas' => function ($query) {
                $query->latest()->limit(10)->with(['mascota', 'veterinario']);
            },
            'facturas'
        ])->findOrFail($id);

        return response()->json($cliente);
    }

    /**
     * Actualizar cliente (CLIENTE solo puede cambiar contraseña, RECEPCIÓN puede todo)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $cliente = Cliente::findOrFail($id);
        
        // CLIENTE: Solo puede actualizar su propio perfil (y solo contraseña)
        if ($user->tipo_usuario === 'cliente') {
            $miCliente = $user->cliente;
            if (!$miCliente || $miCliente->id != $id) {
                return response()->json([
                    'error' => 'No tienes permiso para modificar este perfil'
                ], 403);
            }
            
            // Cliente solo puede cambiar contraseña (se maneja en otro endpoint)
            return response()->json([
                'error' => 'Los clientes solo pueden cambiar su contraseña usando el endpoint /api/cambiar-password'
            ], 403);
            
        } elseif ($user->tipo_usuario !== 'recepcion') {
            // VETERINARIO: No puede editar clientes
            return response()->json([
                'error' => 'No tienes permiso para editar clientes'
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:clientes,user_id,' . $id,
            'nombre' => 'sometimes|required|string|max:150',
            'telefono' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|unique:clientes,email,' . $id, // ✅ Opcional
            'documento_tipo' => 'nullable|string|max:50',
            'documento_num' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'es_walk_in' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $cliente->update($validated);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'actualizar_cliente',
                'tabla' => 'clientes',
                'registro_id' => $cliente->id,
                'cambios' => json_encode($cliente->getChanges()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cliente actualizado exitosamente',
                'cliente' => $cliente
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cliente (SOLO RECEPCIÓN)
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede eliminar clientes
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para eliminar clientes'
            ], 403);
        }
        
        $cliente = Cliente::findOrFail($id);

        // Verificar si tiene mascotas
        if ($cliente->mascotas()->count() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar el cliente porque tiene mascotas asociadas'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'eliminar_cliente',
                'tabla' => 'clientes',
                'registro_id' => $cliente->id,
                'cambios' => json_encode($cliente->toArray()),
            ]);

            $cliente->delete();

            DB::commit();

            return response()->json([
                'message' => 'Cliente eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * � Cambiar contraseña (SOLO CLIENTE puede cambiar su propia contraseña)
     */
    public function cambiarPassword(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'password_actual' => 'required|string',
            'password_nuevo' => 'required|string|min:8|confirmed',
        ]);
        
        // Verificar contraseña actual
        if (!password_verify($validated['password_actual'], $user->password)) {
            return response()->json([
                'error' => 'La contraseña actual es incorrecta'
            ], 422);
        }
        
        // Actualizar contraseña
        $user->password = bcrypt($validated['password_nuevo']);
        $user->save();
        
        return response()->json([
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }
    
    /**
     * �🚀 REGISTRO RÁPIDO: Cliente Walk-In + Mascota en una transacción
     * Endpoint especial para recepcionistas (SOLO RECEPCIÓN)
     */
    public function registroRapido(Request $request)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede hacer registro rápido
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para registrar clientes walk-in'
            ], 403);
        }
        $validated = $request->validate([
            // Datos del Cliente (mínimos requeridos)
            'cliente.nombre' => 'required|string|max:150',
            'cliente.telefono' => 'required|string|max:20',
            'cliente.email' => 'nullable|email|unique:clientes,email',
            'cliente.direccion' => 'nullable|string|max:255',
            'cliente.notas' => 'nullable|string',
            
            // Datos de la Mascota
            'mascota.nombre' => 'required|string|max:100',
            'mascota.especie' => 'required|string|max:50',
            'mascota.raza' => 'nullable|string|max:100',
            'mascota.sexo' => 'required|in:macho,hembra',
            'mascota.fecha_nacimiento' => 'nullable|date',
            'mascota.color' => 'nullable|string|max:50',
            'mascota.peso' => 'nullable|numeric|min:0',
            'mascota.chip_id' => 'nullable|string|max:50',
            'mascota.alergias' => 'nullable|string',
            'mascota.condiciones_medicas' => 'nullable|string',
            'mascota.tipo_sangre' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear cliente walk-in (sin user_id)
            $cliente = Cliente::create([
                'user_id' => null, // ✅ Walk-in sin cuenta
                'es_walk_in' => true, // ✅ Marcado explícitamente
                'nombre' => $validated['cliente']['nombre'],
                'telefono' => $validated['cliente']['telefono'],
                'email' => $validated['cliente']['email'] ?? null,
                'direccion' => $validated['cliente']['direccion'] ?? null,
                'notas' => $validated['cliente']['notas'] ?? null,
                'documento_tipo' => null,
                'documento_num' => null,
            ]);

            // 2. Crear mascota vinculada al cliente
            $mascota = \App\Models\Mascota::create([
                'cliente_id' => $cliente->id,
                'nombre' => $validated['mascota']['nombre'],
                'especie' => $validated['mascota']['especie'],
                'raza' => $validated['mascota']['raza'] ?? null,
                'sexo' => $validated['mascota']['sexo'],
                'fecha_nacimiento' => $validated['mascota']['fecha_nacimiento'] ?? null,
                'color' => $validated['mascota']['color'] ?? null,
                'peso' => $validated['mascota']['peso'] ?? null,
                'chip_id' => $validated['mascota']['chip_id'] ?? null,
                'alergias' => $validated['mascota']['alergias'] ?? null,
                'condiciones_medicas' => $validated['mascota']['condiciones_medicas'] ?? null,
                'tipo_sangre' => $validated['mascota']['tipo_sangre'] ?? null,
                // qr_code se genera automáticamente en el modelo
            ]);

            // 3. Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'registro_rapido_walk_in',
                'tabla' => 'clientes',
                'registro_id' => $cliente->id,
                'cambios' => json_encode([
                    'cliente' => $cliente->toArray(),
                    'mascota' => $mascota->toArray(),
                ]),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente y mascota registrados exitosamente',
                'cliente' => $cliente->load('mascotas'),
                'mascota' => $mascota,
                'qr_code' => $mascota->qr_code,
                'qr_url' => url("/api/qr/lookup/{$mascota->qr_code}"),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Error al registrar: ' . $e->getMessage()
            ], 500);
        }
    }
}
