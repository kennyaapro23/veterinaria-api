<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'cita_id',
        'numero_factura',
        'fecha_emision',
        'subtotal',
        'impuestos',
        'total',
        'estado',
        'metodo_pago',
        'notas',
        'detalles',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'detalles' => 'array',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    /**
     * Historiales médicos incluidos en esta factura
     */
    public function historiales()
    {
        return $this->belongsToMany(HistorialMedico::class, 'factura_historial')
                    ->withPivot('subtotal')
                    ->withTimestamps();
    }
}
