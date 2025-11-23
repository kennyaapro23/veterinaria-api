<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veterinario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'matricula',
        'especialidad',
        'telefono',
        'email',
        'disponibilidad',
    ];

    protected $casts = [
        'disponibilidad' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function historialMedicos()
    {
        return $this->hasMany(HistorialMedico::class, 'realizado_por');
    }

    public function agendasDisponibilidad()
    {
        return $this->hasMany(AgendaDisponibilidad::class);
    }
}
