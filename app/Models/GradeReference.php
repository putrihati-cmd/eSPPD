<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GradeReference extends Model
{
    use HasUuids;

    protected $fillable = [
        'grade_name',
        'uang_harian',
        'uang_representasi',
        'uang_transport_lokal',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'uang_harian' => 'decimal:2',
        'uang_representasi' => 'decimal:2',
        'uang_transport_lokal' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
