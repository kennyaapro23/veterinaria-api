<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\VeterinarioController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FcmTokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Endpoint de prueba (público) para verificar conectividad
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API Veterinaria funcionando correctamente',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0',
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 🔍 QR Code routes (públicas para emergencias)
Route::prefix('qr')->group(function () {
    // Buscar información por QR (acceso público para emergencias)
    Route::get('/lookup/{qrCode}', [QRController::class, 'lookup'])->name('api.qr.lookup');
});

// Authentication routes (Laravel Sanctum - tradicional)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Firebase Authentication routes
Route::prefix('firebase')->group(function () {
    Route::post('/verify', [FirebaseAuthController::class, 'verifyAndSync']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [FirebaseAuthController::class, 'getProfile']);
        Route::put('/profile', [FirebaseAuthController::class, 'updateProfile']);
        Route::post('/fcm-token', [FirebaseAuthController::class, 'registerFcmToken']);
        Route::post('/logout', [FirebaseAuthController::class, 'logout']);
    });
});

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // 📱 QR Code routes (protegidas)
    Route::prefix('qr')->group(function () {
        // Registrar escaneo
        Route::post('/scan-log', [QRController::class, 'logScan']);
        
        // Historial de escaneos
        Route::get('/scan-history/{qrCode}', [QRController::class, 'scanHistory']);
        
        // Estadísticas de escaneos
        Route::get('/scan-stats/{mascotaId}', [QRController::class, 'scanStats']);
    });
    
    // Generar QR para mascota
    Route::get('/mascotas/{id}/qr', [QRController::class, 'generateMascotaQR']);
    
    // Generar QR para cliente
    Route::get('/clientes/{id}/qr', [QRController::class, 'generateClienteQR']);
    
    // FCM Tokens (Firebase Cloud Messaging)        x
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);
    Route::delete('/fcm-token', [FcmTokenController::class, 'destroy']);
    Route::get('/fcm-tokens', [FcmTokenController::class, 'index']);
    Route::delete('/fcm-tokens/all', [FcmTokenController::class, 'destroyAll']);
    
    // Clientes
    Route::apiResource('clientes', ClienteController::class);
    Route::post('/clientes/registro-rapido', [ClienteController::class, 'registroRapido']); // ✅ Walk-in (solo recepción)
    Route::post('/cambiar-password', [ClienteController::class, 'cambiarPassword']); // 🔐 Cambiar contraseña (cualquier usuario)
    
    // Mascotas
    Route::apiResource('mascotas', MascotaController::class);
    
    // Veterinarios
    Route::apiResource('veterinarios', VeterinarioController::class);
    Route::get('/veterinarios/{id}/disponibilidad', [VeterinarioController::class, 'getDisponibilidad']);
    Route::post('/veterinarios/{id}/disponibilidad', [VeterinarioController::class, 'setDisponibilidad']); // Reemplaza TODOS los horarios
    Route::get('/veterinarios/{id}/slots', [VeterinarioController::class, 'getSlotsDisponibles']); // 🆕 Slots listos para frontend
    
    // Gestión individual de horarios (CRUD)
    Route::get('/veterinarios/{id}/horarios', [VeterinarioController::class, 'getHorarios']); // Listar todos
    Route::post('/veterinarios/{id}/horarios', [VeterinarioController::class, 'addHorario']); // Agregar uno
    Route::put('/veterinarios/{veterinarioId}/horarios/{horarioId}', [VeterinarioController::class, 'updateHorario']); // Editar uno
    Route::delete('/veterinarios/{veterinarioId}/horarios/{horarioId}', [VeterinarioController::class, 'deleteHorario']); // Eliminar uno
    Route::patch('/veterinarios/{veterinarioId}/horarios/{horarioId}/toggle', [VeterinarioController::class, 'toggleHorario']); // Activar/Desactivar
    
    // Citas
    Route::apiResource('citas', CitaController::class);
    
    // Servicios
    Route::apiResource('servicios', ServicioController::class);
    Route::get('/servicios-tipos', [ServicioController::class, 'getTipos']);
    
    // Historial Médico
    Route::get('/historial-medico', [HistorialController::class, 'index']);
    Route::post('/historial-medico', [HistorialController::class, 'store']);
    Route::get('/historial-medico/{id}', [HistorialController::class, 'show']);
    // archivos endpoint removed
    
    // Notificaciones
    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::get('/notificaciones/tipos', [NotificacionController::class, 'getTipos']);
    Route::get('/notificaciones/unread-count', [NotificacionController::class, 'getUnreadCount']);
    Route::post('/notificaciones/mark-all-read', [NotificacionController::class, 'markAllAsRead']);
    Route::delete('/notificaciones/delete-read', [NotificacionController::class, 'deleteRead']);
    Route::get('/notificaciones/{id}', [NotificacionController::class, 'show']);
    Route::post('/notificaciones/{id}/mark-read', [NotificacionController::class, 'markAsRead']);
    Route::delete('/notificaciones/{id}', [NotificacionController::class, 'destroy']);
    
    // Facturas
    Route::apiResource('facturas', FacturaController::class);
    Route::post('/facturas/desde-historiales', [FacturaController::class, 'storeFromHistoriales']); // ✅ Facturar desde historiales
    Route::get('/facturas-estadisticas', [FacturaController::class, 'getEstadisticas']);
    Route::get('/generar-numero-factura', [FacturaController::class, 'generateNumeroFactura']);
});
