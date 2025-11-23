<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Models\Notificacion;
use App\Models\FcmToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnviarRecordatoriosCitas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Buscar citas entre ahora+23h y ahora+25h (ventana de 2 horas)
        $inicio = Carbon::now()->addHours(23);
        $fin = Carbon::now()->addHours(25);

        $citas = Cita::with(['cliente.user', 'mascota', 'veterinario'])
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->get();

        Log::info("Enviando recordatorios para {$citas->count()} citas");

        foreach ($citas as $cita) {
            try {
                // Solo enviar si el cliente tiene usuario asociado
                if (!$cita->cliente->user_id) {
                    continue;
                }

                $user_id = $cita->cliente->user_id;
                $fecha_formateada = Carbon::parse($cita->fecha)->format('d/m/Y H:i');

                // Crear notificación en BD
                $notificacion = Notificacion::create([
                    'user_id' => $user_id,
                    'tipo' => 'recordatorio_cita',
                    'titulo' => 'Recordatorio de Cita',
                    'cuerpo' => "Recordatorio: Tienes una cita mañana a las {$fecha_formateada} para {$cita->mascota->nombre}",
                    'leida' => false,
                    'meta' => json_encode([
                        'cita_id' => $cita->id,
                        'mascota_id' => $cita->mascota_id,
                        'veterinario_id' => $cita->veterinario_id,
                    ]),
                    'sent_via' => 'push',
                ]);

                // Obtener tokens FCM del usuario
                $tokens = FcmToken::where('user_id', $user_id)
                    ->pluck('token')
                    ->toArray();

                if (!empty($tokens)) {
                    // Enviar push notification via FCM
                    $this->sendFcmNotification($tokens, [
                        'title' => 'Recordatorio de Cita',
                        'body' => "Cita mañana a las {$fecha_formateada} para {$cita->mascota->nombre}",
                        'data' => [
                            'cita_id' => $cita->id,
                            'type' => 'recordatorio_cita',
                        ],
                    ]);

                    Log::info("Push enviado a {$user_id} para cita {$cita->id}");
                } else {
                    // Fallback: enviar email
                    // TODO: Implementar envío de email
                    // Mail::to($cita->cliente->email)->send(new CitaReminderMail($cita));
                    
                    $notificacion->sent_via = 'email';
                    $notificacion->save();

                    Log::info("Email fallback para usuario {$user_id} - cita {$cita->id}");
                }

            } catch (\Exception $e) {
                Log::error("Error enviando recordatorio para cita {$cita->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Enviar notificación FCM
     */
    private function sendFcmNotification(array $tokens, array $data)
    {
        // TODO: Implementar integración con Firebase Cloud Messaging
        // Requiere: composer require kreait/laravel-firebase
        
        /*
        try {
            $messaging = app('firebase.messaging');
            
            $notification = Notification::create($data['title'], $data['body']);
            
            foreach ($tokens as $token) {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data['data']);
                
                $messaging->send($message);
            }
        } catch (\Exception $e) {
            Log::error("Error FCM: {$e->getMessage()}");
            throw $e;
        }
        */

        // Placeholder: Log para simular envío
        Log::info("FCM notification enviada a " . count($tokens) . " dispositivos", [
            'title' => $data['title'],
            'body' => $data['body'],
        ]);
    }
}
