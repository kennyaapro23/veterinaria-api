<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

if (!function_exists('sendPushNotification')) {
    /**
     * Envía una notificación push a través de Firebase Cloud Messaging
     *
     * @param string $fcmToken Token FCM del dispositivo
     * @param string $title Título de la notificación
     * @param string $body Cuerpo del mensaje
     * @param array $data Datos adicionales (opcional)
     * @return bool
     */
    function sendPushNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $serverKey = env('FCM_SERVER_KEY');

        if (empty($serverKey)) {
            Log::warning('FCM_SERVER_KEY no configurado en .env');
            return false;
        }

        if (empty($fcmToken)) {
            Log::warning('FCM Token vacío, no se puede enviar notificación');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'priority' => 'high',
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                Log::info('Notificación push enviada exitosamente', [
                    'title' => $title,
                    'token' => substr($fcmToken, 0, 20) . '...',
                ]);
                return true;
            } else {
                Log::error('Error al enviar notificación push', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Excepción al enviar notificación push: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('sendPushToUser')) {
    /**
     * Envía notificación push a todos los dispositivos de un usuario
     *
     * @param \App\Models\User $user Usuario destinatario
     * @param string $title Título de la notificación
     * @param string $body Cuerpo del mensaje
     * @param array $data Datos adicionales (opcional)
     * @return int Número de notificaciones enviadas exitosamente
     */
    function sendPushToUser($user, string $title, string $body, array $data = []): int
    {
        $sentCount = 0;

        // Obtener todos los tokens FCM activos del usuario
        $fcmTokens = $user->fcmTokens()->get();

        if ($fcmTokens->isEmpty()) {
            Log::info("Usuario {$user->id} no tiene tokens FCM registrados");
            return 0;
        }

        foreach ($fcmTokens as $tokenRecord) {
            if (sendPushNotification($tokenRecord->token, $title, $body, $data)) {
                $sentCount++;
            }
        }

        Log::info("Notificaciones enviadas a usuario {$user->id}: {$sentCount}/{$fcmTokens->count()}");

        return $sentCount;
    }
}

if (!function_exists('sendPushToMultipleTokens')) {
    /**
     * Envía notificación push a múltiples tokens (broadcast)
     *
     * @param array $fcmTokens Array de tokens FCM
     * @param string $title Título de la notificación
     * @param string $body Cuerpo del mensaje
     * @param array $data Datos adicionales (opcional)
     * @return int Número de notificaciones enviadas exitosamente
     */
    function sendPushToMultipleTokens(array $fcmTokens, string $title, string $body, array $data = []): int
    {
        $serverKey = env('FCM_SERVER_KEY');

        if (empty($serverKey)) {
            Log::warning('FCM_SERVER_KEY no configurado en .env');
            return 0;
        }

        if (empty($fcmTokens)) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => $fcmTokens, // Para múltiples tokens
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'priority' => 'high',
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $successCount = $result['success'] ?? 0;
                
                Log::info("Notificaciones broadcast enviadas: {$successCount}/" . count($fcmTokens));
                return $successCount;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error en broadcast de notificaciones: ' . $e->getMessage());
            return 0;
        }
    }
}
