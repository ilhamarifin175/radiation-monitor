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
 * @property \Carbon\Carbon|null $measured_at
 * @property int|null $latency_ms
 * @property int $is_backfill
 * @property \Carbon\Carbon $created_at
 */
class MonitorDalam extends Model
{
    use HasFactory;

    protected $table = 'monitor_dalam';

    public $timestamps = false;

    protected $casts = [
        'measured_at' => 'datetime:Y-m-d H:i:s',
        'created_at'  => 'datetime:Y-m-d H:i:s',
    ];

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    protected $fillable = [
        'measured_at',
        'seq',
        'cps',
        'usvh',
        'suhu',
        'kelembapan',
        'relay',
        'jaringan',
        'rssi',
        'latency_ms',
        'is_backfill',
    ];
}
