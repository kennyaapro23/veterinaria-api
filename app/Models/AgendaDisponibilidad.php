<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaDisponibilidad extends Model
{
    use HasFactory;

    protected $table = 'agendas_disponibilidad';

    protected $fillable = [
        'veterinario_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'intervalo_minutos',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'hora_inicio' => 'datetime:H:i:s',
        'hora_fin' => 'datetime:H:i:s',
    ];

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }
}
