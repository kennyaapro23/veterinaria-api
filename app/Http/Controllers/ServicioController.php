<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    /**
     * Listar servicios (TODOS pueden ver)
     */
    public function index(Request $request)
    {
        // Todos los roles pueden ver servicios
        $query = Servicio::query();

        // Filtro por tipo
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Búsqueda por nombre o código
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de precio
        if ($request->has('precio_min')) {
            $query->where('precio', '>=', $request->precio_min);
        }

        if ($request->has('precio_max')) {
            $query->where('precio', '<=', $request->precio_max);
        }

        $servicios = $query->orderBy('codigo')->paginate(20);

        return response()->json($servicios);
    }

    /**
     * Crear servicio (SOLO RECEPCIÓN)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede crear servicios
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para crear servicios'
            ], 403);
        }
        
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:servicios,codigo',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:vacuna,tratamiento,baño,consulta,cirugía,otro',
            'duracion_minutos' => 'required|integer|min:5|max:480',
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'requiere_vacuna_info' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $servicio = Servicio::create($validated);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_servicio',
                'tabla' => 'servicios',
                'registro_id' => $servicio->id,
                'cambios' => json_encode($servicio->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Servicio creado exitosamente',
                'servicio' => $servicio
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver servicio (TODOS pueden ver)
     */
    public function show($id)
    {
        // Todos los roles pueden ver servicios
        $servicio = Servicio::with([
            'citas' => function ($query) {
                $query->latest()->limit(10);
            }
        ])->findOrFail($id);

        // Agregar información adicional
        $data = $servicio->toArray();
        $data['es_vacuna'] = $servicio->isVaccine();

        return response()->json($data);
    }

    /**
     * Actualizar servicio (SOLO RECEPCIÓN)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede actualizar servicios
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para actualizar servicios'
            ], 403);
        }
        
        $servicio = Servicio::findOrFail($id);

        $validated = $request->validate([
            'codigo' => 'sometimes|required|string|max:50|unique:servicios,codigo,' . $id,
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion' => 'nullable|string',
            'tipo' => 'sometimes|required|in:vacuna,tratamiento,baño,consulta,cirugía,otro',
            'duracion_minutos' => 'sometimes|required|integer|min:5|max:480',
            'precio' => 'sometimes|required|numeric|min:0|max:99999999.99',
            'requiere_vacuna_info' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $servicio->update($validated);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'actualizar_servicio',
                'tabla' => 'servicios',
                'registro_id' => $servicio->id,
                'cambios' => json_encode($servicio->getChanges()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Servicio actualizado exitosamente',
                'servicio' => $servicio
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar servicio (SOLO RECEPCIÓN)
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        // Solo RECEPCIÓN puede eliminar servicios
        if ($user->tipo_usuario !== 'recepcion') {
            return response()->json([
                'error' => 'No tienes permiso para eliminar servicios'
            ], 403);
        }
        
        $servicio = Servicio::findOrFail($id);

        // Verificar si está siendo usado en citas
        $citasCount = DB::table('cita_servicio')->where('servicio_id', $id)->count();
        
        if ($citasCount > 0) {
            return response()->json([
                'error' => 'No se puede eliminar el servicio porque está asociado a ' . $citasCount . ' cita(s)'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'eliminar_servicio',
                'tabla' => 'servicios',
                'registro_id' => $servicio->id,
                'cambios' => json_encode($servicio->toArray()),
            ]);

            $servicio->delete();

            DB::commit();

            return response()->json([
                'message' => 'Servicio eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar tipos de servicios disponibles
     */
    public function getTipos()
    {
        return response()->json([
            'tipos' => [
                'vacuna',
                'tratamiento',
                'baño',
                'consulta',
                'cirugía',
                'otro'
            ]
        ]);
    }
}
