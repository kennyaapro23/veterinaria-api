<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Especie;

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
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
            
            // ✅ Generar QR automáticamente al crear
            if (empty($model->qr_code)) {
                $model->qr_code = 'VETCARE_PET_' . Str::uuid();
            }
        });
    }

    // ✅ Método para regenerar QR si es necesario
    public function regenerarQR()
    {
        $this->qr_code = 'VETCARE_PET_' . Str::uuid();
        $this->save();
        return $this->qr_code;
    }

    // ✅ Scope para buscar por QR
    public function scopePorQR($query, $qrCode)
    {
        return $query->where('qr_code', $qrCode);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function historialMedicos()
    {
        return $this->hasMany(HistorialMedico::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }


    /**
     * Accessor para calcular edad de la mascota
     */
    public function getEdadAttribute()
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }

        $nacimiento = \Carbon\Carbon::parse($this->fecha_nacimiento);
        $ahora = \Carbon\Carbon::now();

        $years = $nacimiento->diffInYears($ahora);
        $months = $nacimiento->copy()->addYears($years)->diffInMonths($ahora);

        if ($years > 0) {
            return $years . ' año' . ($years > 1 ? 's' : '') . 
                   ($months > 0 ? ' y ' . $months . ' mes' . ($months > 1 ? 'es' : '') : '');
        } else {
            return $months . ' mes' . ($months > 1 ? 'es' : '');
        }
    }
}
