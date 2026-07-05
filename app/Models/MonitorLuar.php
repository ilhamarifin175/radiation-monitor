<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $timestamp
 * @property float $cps
 * @property float $usvh
 * @property float $suhu
 * @property float $kelembapan
 * @property \Carbon\Carbon $created_at
 */
class MonitorLuar extends Model
{
    use HasFactory;

    protected $table = 'monitor_luar';

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
        'cps',
        'usvh',
        'suhu',
        'kelembapan',
    ];
}
