<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;

class FirebaseAuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * Verificar y sincronizar usuario de Firebase
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyAndSync(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'rol' => 'sometimes|in:cliente,veterinario,admin',
            'additional_data' => 'sometimes|array',
        ]);

        try {
            // Verificar el token con Firebase
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->firebase_token);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            
            // Obtener información del usuario de Firebase
            $firebaseUser = $this->firebaseAuth->getUser($firebaseUid);

            // Buscar o crear el usuario en la base de datos local
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                // Crear usuario si no existe
                $user = User::create([
                    'firebase_uid' => $firebaseUid,
                    // The users table uses `name` and `tipo_usuario` columns
                    'name' => $firebaseUser->displayName ?? $request->additional_data['nombre'] ?? 'Usuario',
                    'email' => $firebaseUser->email,
                    'telefono' => $firebaseUser->phoneNumber ?? $request->additional_data['telefono'] ?? null,
                    'tipo_usuario' => $request->rol ?? 'cliente',
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]);

                // Crear perfil según el tipo_usuario (cliente/veterinario)
                if ($user->tipo_usuario === 'cliente') {
                    Cliente::create([
                        'user_id' => $user->id,
                        // Cliente table expects `nombre`
                        'nombre' => $user->name,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'direccion' => $request->additional_data['direccion'] ?? null,
                    ]);
                } elseif ($user->tipo_usuario === 'veterinario') {
                    Veterinario::create([
                        'user_id' => $user->id,
                        'nombre' => $user->name,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'especialidad' => $request->additional_data['especialidad'] ?? 'General',
                        'licencia' => $request->additional_data['licencia'] ?? null,
                    ]);
                }
            }

            // Crear token de Sanctum para la API
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'message' => 'Usuario autenticado exitosamente',
                'user' => $user->load(['cliente', 'veterinario']),
                'sanctum_token' => $token,
                'firebase_user' => [
                    'uid' => $firebaseUser->uid,
                    'email' => $firebaseUser->email,
                    'display_name' => $firebaseUser->displayName,
                    'photo_url' => $firebaseUser->photoUrl,
                    'email_verified' => $firebaseUser->emailVerified,
                ],
            ], 200);

        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'El token de Firebase no es válido o ha expirado'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error de autenticación',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil del usuario
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:20',
            'direccion' => 'sometimes|string|max:500',
            'especialidad' => 'sometimes|string|max:255',
        ]);

        // Map incoming 'nombre' to users.name and update telefono
        $updateData = [];
        if ($request->has('nombre')) {
            $updateData['name'] = $request->input('nombre');
        }
        if ($request->has('telefono')) {
            $updateData['telefono'] = $request->input('telefono');
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Actualizar perfil específico (cliente/veterinario) which store nombre
        if ($user->tipo_usuario === 'cliente' && $user->cliente) {
            $user->cliente->update($request->only(['nombre', 'telefono', 'direccion']));
        } elseif ($user->tipo_usuario === 'veterinario' && $user->veterinario) {
            $user->veterinario->update($request->only(['nombre', 'telefono', 'especialidad']));
        }

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user->fresh()->load(['cliente', 'veterinario']),
        ], 200);
    }

    /**
     * Obtener perfil del usuario autenticado
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        $user = Auth::user()->load(['cliente', 'veterinario']);

        return response()->json([
            'user' => $user,
        ], 200);
    }

    /**
     * Registrar FCM Token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'required|in:android,ios',
        ]);

        $user = Auth::user();

        // Verificar si ya existe el token
        $existingToken = $user->fcm_tokens()
            ->where('token', $request->fcm_token)
            ->first();

        if (!$existingToken) {
            $user->fcm_tokens()->create([
                'token' => $request->fcm_token,
                'device_type' => $request->device_type,
            ]);
        }

        return response()->json([
            'message' => 'Token FCM registrado exitosamente',
        ], 200);
    }

    /**
     * Cerrar sesión (revocar tokens)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Eliminar todos los tokens de Sanctum del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ], 200);
    }
}
