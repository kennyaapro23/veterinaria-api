<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    /**
     * Listar notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $query = Notificacion::where('user_id', auth()->id());

        // Filtro por leída/no leída
        if ($request->has('leida')) {
            $leida = filter_var($request->leida, FILTER_VALIDATE_BOOLEAN);
            $query->where('leida', $leida);
        }

        // Filtro por tipo
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Ordenar por más reciente primero
        $notificaciones = $query->latest()->paginate(20);

        return response()->json($notificaciones);
    }

    /**
     * Ver detalle de notificación
     */
    public function show($id)
    {
        $notificacion = Notificacion::where('user_id', auth()->id())
            ->findOrFail($id);

        // Marcar como leída automáticamente al verla
        if (!$notificacion->leida) {
            $notificacion->update([
                'leida' => true,
                'fecha_lectura' => now()
            ]);
        }

        return response()->json($notificacion);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead($id)
    {
        $notificacion = Notificacion::where('user_id', auth()->id())
            ->findOrFail($id);

        if (!$notificacion->leida) {
            $notificacion->update([
                'leida' => true,
                'fecha_lectura' => now()
            ]);
        }

        return response()->json([
            'message' => 'Notificación marcada como leída',
            'notificacion' => $notificacion
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request)
    {
        $tipos = $request->tipos; // Array opcional de tipos a marcar

        $query = Notificacion::where('user_id', auth()->id())
            ->where('leida', false);

        if ($tipos && is_array($tipos)) {
            $query->whereIn('tipo', $tipos);
        }

        $updated = $query->update([
            'leida' => true,
            'fecha_lectura' => now()
        ]);

        return response()->json([
            'message' => 'Notificaciones marcadas como leídas',
            'count' => $updated
        ]);
    }

    /**
     * Obtener conteo de notificaciones no leídas
     */
    public function getUnreadCount(Request $request)
    {
        $count = Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->count();

        // Conteo por tipo (opcional)
        $countByType = [];
        if ($request->has('by_type') && $request->by_type) {
            $countByType = Notificacion::where('user_id', auth()->id())
                ->where('leida', false)
                ->select('tipo', DB::raw('count(*) as count'))
                ->groupBy('tipo')
                ->pluck('count', 'tipo')
                ->toArray();
        }

        return response()->json([
            'total' => $count,
            'by_type' => $countByType
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy($id)
    {
        $notificacion = Notificacion::where('user_id', auth()->id())
            ->findOrFail($id);

        $notificacion->delete();

        return response()->json([
            'message' => 'Notificación eliminada exitosamente'
        ]);
    }

    /**
     * Eliminar todas las notificaciones leídas
     */
    public function deleteRead()
    {
        $deleted = Notificacion::where('user_id', auth()->id())
            ->where('leida', true)
            ->delete();

        return response()->json([
            'message' => 'Notificaciones leídas eliminadas',
            'count' => $deleted
        ]);
    }

    /**
     * Listar tipos de notificaciones disponibles
     */
    public function getTipos()
    {
        return response()->json([
            'tipos' => [
                'recordatorio_cita',
                'cita_creada',
                'cita_cancelada',
                'cita_modificada',
                'vacuna_proxima',
                'resultado_disponible',
                'mensaje_veterinario',
                'otro'
            ]
        ]);
    }
}
