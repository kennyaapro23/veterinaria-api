<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FcmTokenController extends Controller
{
    /**
     * Guardar o actualizar FCM token del dispositivo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:500',
            'device_type' => 'required|in:android,ios,web',
            'device_name' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $userId = auth()->id();

            // Buscar si ya existe este token
            $fcmToken = FcmToken::where('token', $validated['token'])->first();

            if ($fcmToken) {
                // Actualizar usuario si cambió (puede pasar con dispositivos compartidos)
                $fcmToken->update([
                    'user_id' => $userId,
                    'device_type' => $validated['device_type'],
                    'device_name' => $validated['device_name'] ?? null,
                ]);
            } else {
                // Crear nuevo registro
                $fcmToken = FcmToken::create([
                    'user_id' => $userId,
                    'token' => $validated['token'],
                    'device_type' => $validated['device_type'],
                    'device_name' => $validated['device_name'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Token FCM guardado exitosamente',
                'fcm_token' => $fcmToken
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al guardar token FCM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar FCM token (logout del dispositivo)
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $deleted = FcmToken::where('token', $validated['token'])
            ->where('user_id', auth()->id())
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Token FCM eliminado exitosamente'
            ]);
        } else {
            return response()->json([
                'message' => 'Token no encontrado'
            ], 404);
        }
    }

    /**
     * Listar tokens del usuario autenticado
     */
    public function index()
    {
        $tokens = FcmToken::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'tokens' => $tokens
        ]);
    }

    /**
     * Eliminar todos los tokens del usuario (logout de todos los dispositivos)
     */
    public function destroyAll()
    {
        $deleted = FcmToken::where('user_id', auth()->id())->delete();

        return response()->json([
            'message' => 'Todos los tokens FCM eliminados',
            'count' => $deleted
        ]);
    }
}
