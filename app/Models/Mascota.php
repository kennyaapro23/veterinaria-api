<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

// IMPORTS DE RELACIONES AÑADIDOS/CORREGIDOS
use App\Models\Cliente;
use App\Models\HistorialMedico;
use App\Models\Cita;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mascota extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'nombre',
        'especie',
        'raza',
        'sexo',
        'fecha_nacimiento',
        'color',
        'chip_id',
        'foto_url',
        'qr_code',
        'alergias',
        'condiciones_medicas',
        'tipo_sangre',
        'microchip',
        'public_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    protected $appends = ['edad']; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
            
            if (empty($model->qr_code)) {
                $model->qr_code = 'VETCARE_PET_' . Str::uuid();
            }
        });
    }

    // RELACIONES

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function historialMedicos(): HasMany
    {
        return $this->hasMany(HistorialMedico::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }


    /**
     * Accessor para calcular edad de la mascota
     */
    public function getEdadAttribute(): ?string
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }

        $nacimiento = Carbon::parse($this->fecha_nacimiento);
        $ahora = Carbon::now();

        $years = $nacimiento->diffInYears($ahora);
        $months = $nacimiento->copy()->addYears($years)->diffInMonths($ahora);

        if ($years > 0) {
            $edadString = $years . ' año' . ($years > 1 ? 's' : '');
            if ($months > 0) {
                $edadString .= ' y ' . $months . ' mes' . ($months > 1 ? 'es' : '');
            }
            return $edadString;
        } else {
            return $months . ' mes' . ($months > 1 ? 'es' : '');
        }
    }
    
    // MÉTODOS Y SCOPES ADICIONALES

    public function regenerarQR()
    {
        $this->qr_code = 'VETCARE_PET_' . Str::uuid();
        $this->save();
        return $this->qr_code;
    }

    public function scopePorQR($query, $qrCode)
    {
        return $query->where('qr_code', $qrCode);
    }
}