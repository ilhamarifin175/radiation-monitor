<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $timestamp
 * @property int $seq
 * @property float $cps
 * @property float $usvh
 * @property float $suhu
 * @property float $kelembapan
 * @property string $relay
 * @property string $jaringan
 * @property int|null $rssi
 * @property \Carbon\Carbon $created_at
 */
class MonitorDalam extends Model
{
    use HasFactory;

    protected $table = 'monitor_dalam';

    public $timestamps = false;

    protected $casts = [
        'timestamp'  => 'datetime',
        'created_at' => 'datetime',
    ];

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('timestamp');
    }

    protected $fillable = [
        'timestamp',
        'seq',
        'cps',
        'usvh',
        'suhu',
        'kelembapan',
        'relay',
        'jaringan',
        'rssi',
    ];
}
