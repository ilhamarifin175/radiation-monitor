<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrityStats extends Model
{
    use HasFactory;

    protected $table = 'integrity_stats';

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
        'wifi_terima',
        'wifi_hilang',
        'wifi_pdr',
        'lora_terima',
        'lora_hilang',
        'lora_pdr',
    ];
}
