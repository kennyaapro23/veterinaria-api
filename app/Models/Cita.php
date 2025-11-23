<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'veterinario_id',
        'fecha',
        'duracion_minutos',
        'estado',
        'motivo',
        'notas',
        'created_by',
        // 'lugar' and 'direccion' removed: all appointments are in-clinic now
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function veterinario()
    {
        return $this->belongsTo(Veterinario::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicio')
                    ->withPivot('cantidad', 'precio_unitario', 'notas')
                    ->withTimestamps();
    }

    public function historialMedicos()
    {
        return $this->hasMany(HistorialMedico::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }


    /**
     * Verificar si esta cita se solapa con otra en el rango de tiempo dado
     */
    public function overlaps($start, $duration)
    {
        $start = \Carbon\Carbon::parse($start);
        $end = $start->copy()->addMinutes($duration);

        $this_start = \Carbon\Carbon::parse($this->fecha);
        $this_end = $this_start->copy()->addMinutes($this->duracion_minutos);

        return ($start < $this_end && $end > $this_start);
    }

    /**
     * Scope para obtener citas de un veterinario en una fecha específica
     */
    public function scopeCitasPorVeterinario($query, $veterinario_id, $fecha = null)
    {
        $query->where('veterinario_id', $veterinario_id);

        if ($fecha) {
            $query->whereDate('fecha', $fecha);
        }

        return $query->orderBy('fecha');
    }
}
