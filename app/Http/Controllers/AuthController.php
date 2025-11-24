<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Veterinario;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string',
            // Optional cliente fields (when role == 'cliente')
            'telefono' => 'nullable|string|max:50',
            'documento_tipo' => 'nullable|string|max:50',
            'documento_num' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:1000',
        ]);

        // Check role exists
        if (! Role::where('name', $data['role'])->exists()) {
            return response()->json(['message' => 'Role not found'], 422);
        }

        try {
            $user = DB::transaction(function () use ($data) {
                $user = User::create([
                    'name' => $data['name'],
                    'tipo_usuario' => $data['role'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                // assign role
                $user->assignRole($data['role']);

                // Crear perfil asociado si aplica
                if ($data['role'] === 'cliente') {
                    // Build cliente payload from provided data or fallback to user fields
                    $clientePayload = [
                        'user_id' => $user->id,
                        'nombre' => $user->name,
                        'email' => $user->email,
                    ];

                    if (!empty($data['telefono'])) {
                        $clientePayload['telefono'] = $data['telefono'];
                    }
                    if (!empty($data['documento_tipo'])) {
                        $clientePayload['documento_tipo'] = $data['documento_tipo'];
                    }
                    if (!empty($data['documento_num'])) {
                        $clientePayload['documento_num'] = $data['documento_num'];
                    }
                    if (!empty($data['direccion'])) {
                        $clientePayload['direccion'] = $data['direccion'];
                    }
                    if (!empty($data['notas'])) {
                        $clientePayload['notas'] = $data['notas'];
                    }

                    Cliente::create($clientePayload);
                } elseif ($data['role'] === 'veterinario') {
                    Veterinario::create([
                        'user_id' => $user->id,
                        'nombre' => $user->name,
                        'email' => $user->email,
                        'especialidad' => 'General',
                    ]);
                }

                return $user;
            });

            // create token after successful commit
            $token = $user->createToken('api-token')->plainTextToken;

            // load cliente relation if any
            $user->load('cliente');

            return response()->json(['user' => $user, 'token' => $token], 201);
        } catch (\Exception $e) {
            \Log::error('Register error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error creating account'], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Ensure cliente relation is loaded when returning the user
        $user->load('cliente');

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
