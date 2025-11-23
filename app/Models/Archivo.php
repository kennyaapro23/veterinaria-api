<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'relacionado_tipo',
        'relacionado_id',
        'nombre',
        'url',
        'tipo_mime',
        'size',
        'uploaded_by',
    ];

    public function relacionado()
    {
        // Use Spanish column names if the DB uses 'relacionado_tipo' and 'relacionado_id'
        return $this->morphTo(null, 'relacionado_tipo', 'relacionado_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
