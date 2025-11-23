<?php

namespace App\Http\Controllers;

use App\Models\Veterinario;
use App\Models\AgendaDisponibilidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VeterinarioController extends Controller
{
    /**
     * Listar veterinarios
     */
    public function index(Request $request)
    {
        $query = Veterinario::with(['user']);

        // Búsqueda por nombre o especialidad
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%")
                  ->orWhere('matricula', 'like', "%{$search}%");
            });
        }

        // Filtro por especialidad
        if ($request->has('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        $veterinarios = $query->orderBy('nombre')->paginate(20);

        return response()->json($veterinarios);
    }

    /**
     * Crear veterinario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:veterinarios,user_id',
            'nombre' => 'required|string|max:150',
            'matricula' => 'nullable|string|max:50|unique:veterinarios,matricula',
            'especialidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'disponibilidad' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $veterinario = Veterinario::create($validated);

            // Crear horarios por defecto (Lunes a Viernes, 9:00-18:00, intervalos de 30 min)
            $this->crearHorariosDefecto($veterinario->id);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'crear_veterinario',
                'tabla' => 'veterinarios',
                'registro_id' => $veterinario->id,
                'cambios' => json_encode($veterinario->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Veterinario creado exitosamente',
                'veterinario' => $veterinario->load('agendasDisponibilidad')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear veterinario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver veterinario
     */
    public function show($id)
    {
        $veterinario = Veterinario::with([
            'user',
            'agendasDisponibilidad',
            'citas' => function ($query) {
                $query->where('fecha', '>=', now())
                      ->orderBy('fecha')
                      ->limit(20)
                      ->with(['mascota', 'cliente']);
            }
        ])->findOrFail($id);

        return response()->json($veterinario);
    }

    /**
     * Actualizar veterinario
     */
    public function update(Request $request, $id)
    {
        $veterinario = Veterinario::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:veterinarios,user_id,' . $id,
            'nombre' => 'sometimes|required|string|max:150',
            'matricula' => 'nullable|string|max:50|unique:veterinarios,matricula,' . $id,
            'especialidad' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'disponibilidad' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $veterinario->update($validated);

            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'actualizar_veterinario',
                'tabla' => 'veterinarios',
                'registro_id' => $veterinario->id,
                'cambios' => json_encode($veterinario->getChanges()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Veterinario actualizado exitosamente',
                'veterinario' => $veterinario
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al actualizar veterinario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar veterinario
     */
    public function destroy($id)
    {
        $veterinario = Veterinario::findOrFail($id);

        // Verificar si tiene citas futuras
        $citasFuturas = $veterinario->citas()->where('fecha', '>=', now())->count();
        if ($citasFuturas > 0) {
            return response()->json([
                'error' => 'No se puede eliminar el veterinario porque tiene citas futuras programadas'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Auditoría
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'accion' => 'eliminar_veterinario',
                'tabla' => 'veterinarios',
                'registro_id' => $veterinario->id,
                'cambios' => json_encode($veterinario->toArray()),
            ]);

            $veterinario->delete();

            DB::commit();

            return response()->json([
                'message' => 'Veterinario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al eliminar veterinario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener disponibilidad de un veterinario
     */
    public function getDisponibilidad($id, Request $request)
    {
        $veterinario = Veterinario::findOrFail($id);

        // Obtener fecha (por defecto hoy)
        $fecha = $request->query('fecha', now()->format('Y-m-d'));
        $diaSemana = \Carbon\Carbon::parse($fecha)->dayOfWeek;

        // Obtener horarios configurados para ese día
        $agendas = AgendaDisponibilidad::where('veterinario_id', $id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        // Obtener citas ya agendadas para ese día
        $citas = \App\Models\Cita::where('veterinario_id', $id)
            ->whereDate('fecha', $fecha)
            ->whereNotIn('estado', ['cancelada'])
            ->select('fecha', 'duracion_minutos')
            ->get();

        return response()->json([
            'veterinario' => $veterinario->only(['id', 'nombre', 'especialidad']),
            'fecha' => $fecha,
            'dia_semana' => $diaSemana,
            'horarios_configurados' => $agendas,
            'citas_agendadas' => $citas,
        ]);
    }

    /**
     * Crear horarios por defecto para un veterinario
     * Lunes a Viernes (1-5), 9:00-18:00, intervalos de 30 minutos
     */
    private function crearHorariosDefecto($veterinarioId)
    {
        $horariosDefecto = [
            ['dia_semana' => 1, 'nombre_dia' => 'Lunes'],
            ['dia_semana' => 2, 'nombre_dia' => 'Martes'],
            ['dia_semana' => 3, 'nombre_dia' => 'Miércoles'],
            ['dia_semana' => 4, 'nombre_dia' => 'Jueves'],
            ['dia_semana' => 5, 'nombre_dia' => 'Viernes'],
        ];

        foreach ($horariosDefecto as $dia) {
            AgendaDisponibilidad::create([
                'veterinario_id' => $veterinarioId,
                'dia_semana' => $dia['dia_semana'],
                'hora_inicio' => '09:00',
                'hora_fin' => '18:00',
                'intervalo_minutos' => 30,
                'activo' => true,
            ]);
        }
    }

    /**
     * Autorizar edición/gestión de horarios
     * Solo permite a: usuario tipo 'recepcion' o al veterinario dueño (user_id)
     */
    private function authorizeEdit($veterinarioId)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'No autorizado');
        }

        // Recepción puede editar cualquier horario
        if (isset($user->tipo_usuario) && $user->tipo_usuario === 'recepcion') {
            return;
        }

        // Si es veterinario, solo puede editar su propia agenda
        if (isset($user->tipo_usuario) && $user->tipo_usuario === 'veterinario') {
            $miVet = Veterinario::where('user_id', $user->id)->first();
            if (!$miVet || $miVet->id != $veterinarioId) {
                abort(403, 'No autorizado para modificar este horario');
            }
            return;
        }

        // Otros tipos no permitidos
        abort(403, 'No autorizado');
    }

    /**
     * Obtener slots de tiempo disponibles u ocupados para un veterinario en una fecha
     * Devuelve un calendario listo para pintar en el frontend
     */
    public function getSlotsDisponibles($id, Request $request)
    {
        $veterinario = Veterinario::findOrFail($id);

        // Obtener fecha (por defecto hoy)
        $fecha = $request->query('fecha', now()->format('Y-m-d'));
        $diaSemana = \Carbon\Carbon::parse($fecha)->dayOfWeek;

        // Obtener horarios configurados para ese día
        $agendas = AgendaDisponibilidad::where('veterinario_id', $id)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($agendas->isEmpty()) {
            return response()->json([
                'veterinario' => $veterinario->only(['id', 'nombre', 'especialidad']),
                'fecha' => $fecha,
                'mensaje' => 'No hay horarios configurados para este día',
                'slots' => []
            ]);
        }

        // Obtener citas ya agendadas para ese día
        $citas = \App\Models\Cita::where('veterinario_id', $id)
            ->whereDate('fecha', $fecha)
            ->whereNotIn('estado', ['cancelada'])
            ->get();

        // Generar todos los slots posibles
        $slots = [];
        foreach ($agendas as $agenda) {
            $horaActual = \Carbon\Carbon::parse($fecha . ' ' . $agenda->hora_inicio);
            $horaFin = \Carbon\Carbon::parse($fecha . ' ' . $agenda->hora_fin);

            while ($horaActual->lt($horaFin)) {
                $slotInicio = $horaActual->copy();
                $slotFin = $horaActual->copy()->addMinutes($agenda->intervalo_minutos);

                // Verificar si este slot está ocupado por alguna cita
                $ocupado = false;
                $citaInfo = null;

                foreach ($citas as $cita) {
                    $citaInicio = \Carbon\Carbon::parse($cita->fecha);
                    $citaFin = $citaInicio->copy()->addMinutes($cita->duracion_minutos);

                    // Verificar solapamiento
                    if ($slotInicio->lt($citaFin) && $slotFin->gt($citaInicio)) {
                        $ocupado = true;
                        $citaInfo = [
                            'id' => $cita->id,
                            'cliente' => $cita->cliente->nombre ?? 'Sin nombre',
                            'mascota' => $cita->mascota->nombre ?? 'Sin nombre',
                            'motivo' => $cita->motivo,
                            'estado' => $cita->estado,
                        ];
                        break;
                    }
                }

                $slots[] = [
                    'hora_inicio' => $slotInicio->format('H:i'),
                    'hora_fin' => $slotFin->format('H:i'),
                    'disponible' => !$ocupado,
                    'cita' => $citaInfo,
                ];

                $horaActual->addMinutes($agenda->intervalo_minutos);
            }
        }

        return response()->json([
            'veterinario' => $veterinario->only(['id', 'nombre', 'especialidad']),
            'fecha' => $fecha,
            'dia_semana' => $diaSemana,
            'slots' => $slots,
        ]);
    }

    /**
     * Configurar horarios de disponibilidad (REEMPLAZA TODOS)
     */
    public function setDisponibilidad(Request $request, $id)
    {
        $veterinario = Veterinario::findOrFail($id);
        // Verificar permisos: solo recepción o el propio veterinario pueden cambiar su disponibilidad
        $this->authorizeEdit($id);

        $validated = $request->validate([
            'horarios' => 'required|array',
            'horarios.*.dia_semana' => 'required|integer|between:0,6',
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i|after:horarios.*.hora_inicio',
            'horarios.*.intervalo_minutos' => 'required|integer|min:10|max:120',
            'horarios.*.activo' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Eliminar horarios anteriores
            AgendaDisponibilidad::where('veterinario_id', $id)->delete();

            // Crear nuevos horarios
            foreach ($validated['horarios'] as $horario) {
                AgendaDisponibilidad::create([
                    'veterinario_id' => $id,
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin'],
                    'intervalo_minutos' => $horario['intervalo_minutos'],
                    'activo' => $horario['activo'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Horarios de disponibilidad configurados exitosamente',
                'horarios' => $veterinario->agendasDisponibilidad
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al configurar horarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los horarios de un veterinario
     */
    public function getHorarios($id)
    {
        $veterinario = Veterinario::findOrFail($id);

        $horarios = AgendaDisponibilidad::where('veterinario_id', $id)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json([
            'veterinario' => $veterinario->only(['id', 'nombre', 'especialidad']),
            'horarios' => $horarios,
            'total' => $horarios->count(),
        ]);
    }

    /**
     * Agregar un horario individual sin eliminar los demás
     */
    public function addHorario(Request $request, $id)
    {
        $veterinario = Veterinario::findOrFail($id);
        // Verificar permisos: solo recepción o el propio veterinario
        $this->authorizeEdit($id);

        $validated = $request->validate([
            'dia_semana' => 'required|integer|between:0,6',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'intervalo_minutos' => 'required|integer|min:10|max:120',
            'activo' => 'boolean',
        ]);

        try {
            $horario = AgendaDisponibilidad::create([
                'veterinario_id' => $id,
                'dia_semana' => $validated['dia_semana'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'intervalo_minutos' => $validated['intervalo_minutos'],
                'activo' => $validated['activo'] ?? true,
            ]);

            return response()->json([
                'message' => 'Horario agregado exitosamente',
                'horario' => $horario
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al agregar horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un horario específico
     */
    public function updateHorario(Request $request, $veterinarioId, $horarioId)
    {
        // Verificar permisos: solo recepción o el propio veterinario
        $this->authorizeEdit($veterinarioId);

        $horario = AgendaDisponibilidad::where('veterinario_id', $veterinarioId)
            ->where('id', $horarioId)
            ->firstOrFail();

        $validated = $request->validate([
            'dia_semana' => 'sometimes|integer|between:0,6',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin' => 'sometimes|date_format:H:i|after:hora_inicio',
            'intervalo_minutos' => 'sometimes|integer|min:10|max:120',
            'activo' => 'sometimes|boolean',
        ]);

        try {
            $horario->update($validated);

            return response()->json([
                'message' => 'Horario actualizado exitosamente',
                'horario' => $horario
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un horario específico
     */
    public function deleteHorario($veterinarioId, $horarioId)
    {
        // Verificar permisos: solo recepción o el propio veterinario
        $this->authorizeEdit($veterinarioId);

        $horario = AgendaDisponibilidad::where('veterinario_id', $veterinarioId)
            ->where('id', $horarioId)
            ->firstOrFail();

        try {
            $horario->delete();

            return response()->json([
                'message' => 'Horario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar/Desactivar un horario sin eliminarlo
     */
    public function toggleHorario($veterinarioId, $horarioId)
    {
        // Verificar permisos: solo recepción o el propio veterinario
        $this->authorizeEdit($veterinarioId);

        $horario = AgendaDisponibilidad::where('veterinario_id', $veterinarioId)
            ->where('id', $horarioId)
            ->firstOrFail();

        try {
            $horario->update(['activo' => !$horario->activo]);

            return response()->json([
                'message' => $horario->activo ? 'Horario activado' : 'Horario desactivado',
                'horario' => $horario
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cambiar estado del horario: ' . $e->getMessage()
            ], 500);
        }
    }
}
