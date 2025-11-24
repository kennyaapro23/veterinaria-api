<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Cliente;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class MascotaController extends Controller
{
    /**
     * Listar mascotas
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Asegurar que si es cliente y no tiene perfil, se cree automáticamente
        if ($user->tipo_usuario === 'cliente' && !$user->cliente) {
            Cliente::create([
                'user_id' => $user->id,
                'nombre' => $user->name ?? 'Cliente',
                'email' => $user->email ?? null,
                'es_walk_in' => false,
            ]);
            // refrescar relación
            $user->load('cliente');
        }

        // Authorization: anyone with a role that can view mascotas
        $this->authorize('viewAny', Mascota::class);
        $query = Mascota::with(['cliente']);

        // Si el usuario es CLIENTE, solo mostrar SUS mascotas
        if ($user->tipo_usuario === 'cliente') {
            // Buscar el cliente asociado al usuario
            $cliente = $user->cliente;
            
            if (!$cliente) {
                return response()->json([
                    'error' => 'No tienes un perfil de cliente asociado',
                    'mascotas' => []
                ], 200);
            }
            
            // Filtrar solo las mascotas de este cliente
            $query->where('cliente_id', $cliente->id);
        }
        
        // Si es VETERINARIO o RECEPCIÓN, puede ver todas las mascotas
        // (no se aplica ningún filtro adicional)

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

        return response()->json($mascotas);
    }

    /**
     * Crear mascota
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $this->authorize('create', Mascota::class);
        
        // Si es CLIENTE, usar automáticamente su cliente_id
        // (no puede crear mascotas para otros clientes)
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
            
            // Validación sin requerir cliente_id (se asigna automáticamente)
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'especie' => 'required|string|max:50',
                'raza' => 'nullable|string|max:100',
                'sexo' => 'required|in:macho,hembra,desconocido',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'color' => 'nullable|string|max:50',
                'chip_id' => 'nullable|string|max:50|unique:mascotas,chip_id',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
            ]);
            
            // Asignar automáticamente el cliente_id del usuario autenticado
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
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
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

            // Auditoría
            \App\Models\AuditLog::create([
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

        // Policy will check permissions (cliente only their own; vet/recepcion can view)
        $this->authorize('view', $mascota);

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

        $this->authorize('update', $mascota);

        $validated = $request->validate([
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'nombre' => 'sometimes|required|string|max:100',
            'especie' => 'sometimes|required|string|max:50',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'sometimes|required|in:macho,hembra,desconocido',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'color' => 'nullable|string|max:50',
            'chip_id' => 'nullable|string|max:50|unique:mascotas,chip_id,' . $id,
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

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
            \App\Models\AuditLog::create([
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

        $this->authorize('delete', $mascota);

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
            \App\Models\AuditLog::create([
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
            return response()->json([
                'error' => 'Error al eliminar mascota: ' . $e->getMessage()
            ], 500);
        }
    }
}
