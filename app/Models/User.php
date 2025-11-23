<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firebase_uid',
        'name',
        'email',
        'password',
        'telefono',
        'tipo_usuario',
        
        'perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes to append to model JSON form
     * (adds veterinarian_id automatically when present)
     */
    protected $appends = ['veterinario_id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'perfil' => 'array',
        ];
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    public function veterinario()
    {
        return $this->hasOne(Veterinario::class);
    }

    /**
     * Accessor to expose veterinario_id on the serialized user
     * Returns the related Veterinario.id if exists, creates one if missing
     */
    public function getVeterinarioIdAttribute()
    {
        if ($this->tipo_usuario !== 'veterinario') {
            return null;
        }

        // eager loaded relation or lazy load
        $vet = $this->getRelationValue('veterinario');
        if ($vet) {
            return $vet->id;
        }

        // try to find by user_id
        $vet = \App\Models\Veterinario::where('user_id', $this->id)->first();
        
        // If not found, create automatically with default values
        if (!$vet) {
            $vet = \App\Models\Veterinario::create([
                'user_id' => $this->id,
                'nombre' => $this->name,
                'especialidad' => 'General',
                'email' => $this->email,
                'telefono' => $this->telefono,
            ]);
            
            // Crear horarios por defecto usando el método del controlador
            $this->crearHorariosDefecto($vet->id);
        }
        
        return $vet->id;
    }

    /**
     * Crear horarios por defecto para un veterinario
     * Lunes a Viernes (1-5), 9:00-18:00, intervalos de 30 minutos
     */
    private function crearHorariosDefecto($veterinarioId)
    {
        $horariosDefecto = [
            ['dia_semana' => 1],
            ['dia_semana' => 2],
            ['dia_semana' => 3],
            ['dia_semana' => 4],
            ['dia_semana' => 5],
        ];

        foreach ($horariosDefecto as $dia) {
            \App\Models\AgendaDisponibilidad::create([
                'veterinario_id' => $veterinarioId,
                'dia_semana' => $dia['dia_semana'],
                'hora_inicio' => '09:00',
                'hora_fin' => '18:00',
                'intervalo_minutos' => 30,
                'activo' => true,
            ]);
        }
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
