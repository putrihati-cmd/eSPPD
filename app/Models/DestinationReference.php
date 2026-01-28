<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DestinationReference extends Model
{
    use HasUuids;

    protected $fillable = [
        'kota',
        'provinsi',
        'jarak_km',
        'akomodasi_rate',
        'luar_negeri',
        'is_active',
    ];

    protected $casts = [
        'jarak_km' => 'integer',
        'akomodasi_rate' => 'decimal:2',
        'luar_negeri' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDomestic($query)
    {
        return $query->where('luar_negeri', false);
    }

    public function scopeInternational($query)
    {
        return $query->where('luar_negeri', true);
    }

    public function getFullNameAttribute(): string
    {
        return $this->provinsi ? "{$this->kota}, {$this->provinsi}" : $this->kota;
    }
}
