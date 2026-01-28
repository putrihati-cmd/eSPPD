<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApprovalRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'organization_id',
        'unit_id',
        'level',
        'role',
        'approver_id',
        'threshold_amount',
        'is_active',
        'order',
    ];

    protected $casts = [
        'threshold_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where(function ($q) use ($unitId) {
            $q->whereNull('unit_id')
              ->orWhere('unit_id', $unitId);
        });
    }
}
