<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    use HasFactory;

    protected $fillable = [
        'mascota_id',
        'cita_id',
        'fecha',
        'tipo',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'realizado_por',
        'archivos_meta',
        'facturado',
        'factura_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'archivos_meta' => 'array',
        'facturado' => 'boolean',
        'diagnostico' => 'array',
        'tratamiento' => 'array',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function realizadoPor()
    {
        return $this->belongsTo(Veterinario::class, 'realizado_por');
    }


    /**
     * Servicios aplicados en esta consulta
     */
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'historial_servicio')
                    ->withPivot('cantidad', 'precio_unitario', 'notas')
                    ->withTimestamps();
    }

    /**
     * Calcular costo total de los servicios aplicados
     */
    public function getTotalServiciosAttribute()
    {
        return $this->servicios->sum(function ($servicio) {
            return $servicio->pivot->cantidad * $servicio->pivot->precio_unitario;
        });
    }

    /**
     * Factura asociada (si ya fue facturado)
     */
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    /**
     * Facturas que incluyen este historial (relación N:N)
     */
    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'factura_historial')
                    ->withPivot('subtotal')
                    ->withTimestamps();
    }
}
