<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FirebaseAuthMiddleware
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el token del header Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Token no proporcionado',
                'message' => 'Debes incluir el token en el header Authorization'
            ], 401);
        }

        try {
            // Verificar el token con Firebase
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($token);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            
            // Obtener información del usuario de Firebase
            $firebaseUser = $this->firebaseAuth->getUser($firebaseUid);

            // Buscar o crear el usuario en la base de datos local
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                // Crear usuario si no existe
                $user = User::create([
                    'firebase_uid' => $firebaseUid,
                    // DB users table has `name` and `tipo_usuario` columns
                    'name' => $firebaseUser->displayName ?? 'Usuario',
                    'email' => $firebaseUser->email,
                    'telefono' => $firebaseUser->phoneNumber,
                    'tipo_usuario' => 'cliente', // Rol por defecto
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]);
            }

            // Autenticar al usuario en Laravel
            Auth::login($user);
            
            // Agregar el usuario al request para acceso posterior
            $request->merge(['firebase_user' => $firebaseUser]);
            $request->merge(['user_id' => $user->id]);

            return $next($request);

        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'El token de Firebase no es válido o ha expirado',
                'details' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error de autenticación',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
