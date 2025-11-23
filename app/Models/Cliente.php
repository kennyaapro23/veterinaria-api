<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'telefono',
        'email',
        'documento_tipo',
        'documento_num',
        'direccion',
        'notas',
        'es_walk_in', // ✅ Cliente sin cuenta registrada
    ];

    protected $casts = [
        'es_walk_in' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mascotas()
    {
        return $this->hasMany(Mascota::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
