<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransportReference extends Model
{
    use HasUuids;

    protected $fillable = [
        'jenis',
        'rate_per_km',
        'biaya_tetap',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'rate_per_km' => 'decimal:2',
        'biaya_tetap' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateCost(int $jarak_km): float
    {
        return $this->biaya_tetap + ($this->rate_per_km * $jarak_km);
    }
}
