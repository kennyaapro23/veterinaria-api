<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Cliente;
use App\Models\User;
use App\Models\AuditLog; // 💡 Agregado: Import para la clase de auditoría.
use App\Http\Resources\MascotaResource; // 💡 Agregado: Para el formato API estándar.
use Illuminate\Auth\Access\AuthorizationException; // 💡 Agregado: Para capturar errores de Policy.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class MascotaController extends Controller
{
    /**
     * Listar mascotas
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user) {
                // El middleware Sanctum debería manejar esto, pero es una buena guardia.
                \Log::warning('Unauthenticated request to MascotaController@index');
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            // --- Punto Crítico 1: Acceso a propiedades del User ---
            // Verifica que la tabla 'users' tenga la columna 'tipo_usuario' y que el valor sea correcto.
            // Si $user->cliente es null, se crea automáticamente.
            if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
                Cliente::create([
                    'user_id' => $user->id,
                    'nombre' => $user->name ?? 'Cliente',
                    'email' => $user->email ?? null,
                    'es_walk_in' => false,
                ]);
                $user->load('cliente'); // Refrescar la relación después de crear
            }
            
            // --- Punto Crítico 2: Authorization Policy ---
            // Si el error 500 viene de aquí, se capturará y devolverá 403.
            $this->authorize('viewAny', Mascota::class); 

            $query = Mascota::with(['cliente']);

            // Si el usuario es CLIENTE, solo mostrar SUS mascotas
            if ($user->tipo_usuario === 'cliente') {
                $cliente = $user->cliente;
                
                if (!$cliente) {
                    // Esto no debería pasar después del auto-creado, pero lo mantenemos.
                    return response()->json([
                        'error' => 'No tienes un perfil de cliente asociado',
                        'mascotas' => []
                    ], 200);
                }
                
                $query->where('cliente_id', $cliente->id);
            }
            
            // Filtro manual por cliente_id (para veterinarios/recepción)
            if ($request->has('cliente_id') && $user->tipo_usuario !== 'cliente') {
                $query->where('cliente_id', $request->cliente_id);
            }

            // Búsqueda por nombre o especie
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('especie', 'like', "%{$search}%")
                      ->orWhere('raza', 'like', "%{$search}%")
                      ->orWhere('chip_id', 'like', "%{$search}%");
                });
            }

            $mascotas = $query->orderBy('created_at', 'desc')->paginate(20);

            // 💡 Usamos MascotaResource para estandarizar el formato de salida
            return MascotaResource::collection($mascotas);
            
        } catch (AuthorizationException $e) {
             // 💡 Si falla la Policy, devuelve 403, no 500 genérico.
            return response()->json([
                'error' => 'No tienes permiso para ver esta información. (Policy Error)',
            ], 403);
        } catch (\Exception $e) {
            \Log::error('MascotaController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Server error while listing mascotas',
            ], 500);
        }
    }

    /**
     * Crear mascota
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // --- Punto Crítico 2: Authorization Policy ---
        try {
            $this->authorize('create', Mascota::class);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error' => 'No tienes permiso para crear mascotas (Policy Error)',
            ], 403);
        }

        // Si es CLIENTE, usar automáticamente su cliente_id
        if ($user->tipo_usuario === 'cliente') {
            $cliente = $user->cliente;
            
            if (!$cliente) {
                // Si el usuario cliente no tiene perfil de Cliente, crear uno automáticamente
                $cliente = Cliente::create([
                    'user_id' => $user->id,
                    'nombre' => $user->name ?? 'Cliente',
                    'email' => $user->email ?? null,
                    'es_walk_in' => false,
                ]);
            }
            
            // NOTA: Flutter está enviando JSON (Content-Type: application/json)
            // Si envías archivos (como 'foto') debes usar 'multipart/form-data'. 
            // Si el cliente envía JSON, la foto NO se subirá correctamente.
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'especie' => 'required|string|max:50',
                'raza' => 'nullable|string|max:100',
                'sexo' => 'required|in:macho,hembra,desconocido',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'color' => 'nullable|string|max:50',
                'chip_id' => 'nullable|string|max:50|unique:mascotas,chip_id', 
                // La validación de 'foto' fallará si no es 'multipart/form-data'
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', 
            ]);
            
            $validated['cliente_id'] = $cliente->id;
        } else {
            // Veterinario/Recepción pueden especificar el cliente_id
            $validated = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'nombre' => 'required|string|max:100',
                'especie' => 'required|string|max:50',
                'raza' => 'nullable|string|max:100',
                'sexo' => 'required|in:macho,hembra,desconocido',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'color' => 'nullable|string|max:50',
                'chip_id' => 'nullable|string|max:50|unique:mascotas,chip_id',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            ]);
        }

        DB::beginTransaction();
        try {
            // Subir foto si existe
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('mascotas', 'public');
                $validated['foto_url'] = Storage::url($path);
            }

            $mascota = Mascota::create($validated);

            // Auditoría (AuditLog ya importado)
            AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_mascota',
                'tabla' => 'mascotas',
                'registro_id' => $mascota->id,
                'cambios' => json_encode($mascota->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Mascota creada exitosamente',
                'mascota' => $mascota->load('cliente')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('MascotaController@store transaction error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Error al crear mascota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver mascota
     */
    public function show($id)
    {
        $user = auth()->user();

        // Asegurar cliente existente para policies que lo requieran
        if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
            Cliente::create([
                'user_id' => $user->id,
                'nombre' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'es_walk_in' => false,
            ]);
            $user->load('cliente');
        }

        $mascota = Mascota::with([
            'cliente',
            'historialMedicos' => function ($query) {
                $query->latest()->with('realizadoPor');
            },
            'citas' => function ($query) {
                $query->latest()->limit(10)->with(['veterinario', 'servicios']);
            }
        ])->findOrFail($id);

        try {
            // Policy will check permissions (cliente only their own; vet/recepcion can view)
            $this->authorize('view', $mascota);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error' => 'No tienes permiso para ver esta mascota (Policy Error)',
            ], 403);
        }

        // Agregar edad calculada
        $data = $mascota->toArray();
        $data['edad'] = $mascota->edad;

        return response()->json($data);
    }

    /**
     * Actualizar mascota
     */
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
        $mascota = Mascota::findOrFail($id);

        try {
            $this->authorize('update', $mascota);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error' => 'No tienes permiso para actualizar esta mascota (Policy Error)',
            ], 403);
        }

        // Reglas de validación
        $rules = [
            'nombre' => 'sometimes|required|string|max:100',
            'especie' => 'sometimes|required|string|max:50',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'sometimes|required|in:macho,hembra,desconocido',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'color' => 'nullable|string|max:50',
            'chip_id' => 'nullable|string|max:50|unique:mascotas,chip_id,' . $id,
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];

        if ($user->tipo_usuario !== 'cliente') {
             // Solo si no es cliente puede cambiar el cliente_id
            $rules['cliente_id'] = 'sometimes|required|exists:clientes,id'; 
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Actualizar foto si se sube una nueva
            if ($request->hasFile('foto')) {
                // Eliminar foto anterior si existe
                if ($mascota->foto_url) {
                    $oldPath = str_replace('/storage/', '', $mascota->foto_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('foto')->store('mascotas', 'public');
                $validated['foto_url'] = Storage::url($path);
            }

            $mascota->update($validated);

            // Auditoría
            AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'actualizar_mascota',
                'tabla' => 'mascotas',
                'registro_id' => $mascota->id,
                'cambios' => json_encode($mascota->getChanges()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Mascota actualizada exitosamente',
                'mascota' => $mascota->load('cliente')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('MascotaController@update transaction error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Error al actualizar mascota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar mascota
     */
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
        $mascota = Mascota::findOrFail($id);

        try {
            $this->authorize('delete', $mascota);
        } catch (AuthorizationException $e) {
            return response()->json([
                'error' => 'No tienes permiso para eliminar esta mascota (Policy Error)',
            ], 403);
        }

        // Verificar si tiene historial médico o citas
        if ($mascota->historialMedicos()->count() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar la mascota porque tiene historial médico asociado'
            ], 422);
        }

        if ($mascota->citas()->count() > 0) {
            return response()->json([
                'error' => 'No se puede eliminar la mascota porque tiene citas asociadas'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Eliminar foto si existe
            if ($mascota->foto_url) {
                $oldPath = str_replace('/storage/', '', $mascota->foto_url);
                Storage::disk('public')->delete($oldPath);
            }

            // Auditoría
            AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'eliminar_mascota',
                'tabla' => 'mascotas',
                'registro_id' => $mascota->id,
                'cambios' => json_encode($mascota->toArray()),
            ]);

            $mascota->delete();

            DB::commit();

            return response()->json([
                'message' => 'Mascota eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('MascotaController@destroy transaction error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Error al eliminar mascota: ' . $e->getMessage()
            ], 500);
        }
    }
}