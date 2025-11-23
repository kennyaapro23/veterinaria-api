<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRScanLog extends Model
{
    use HasFactory;

    // ✅ Especificar nombre correcto de tabla
    protected $table = 'qr_scan_logs';

    protected $fillable = [
        'qr_code',
        'scanned_by',
        'ip_address',
        'user_agent',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    // Relación con usuario que escaneó
    public function usuario()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // ✅ Método estático para registrar escaneo
    public static function registrar($qrCode, $userId = null)
    {
        return self::create([
            'qr_code' => $qrCode,
            'scanned_by' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'scanned_at' => now(),
        ]);
    }
}
