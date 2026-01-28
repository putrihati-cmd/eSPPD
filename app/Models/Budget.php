<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'year',
        'code',
        'name',
        'total_budget',
        'used_budget',
        'is_active',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'used_budget' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function spds(): HasMany
    {
        return $this->hasMany(Spd::class);
    }

    /**
     * Get available budget
     */
    public function getAvailableBudgetAttribute(): float
    {
        return (float) $this->total_budget - (float) $this->used_budget;
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_budget == 0) return 0;
        return round(((float) $this->used_budget / (float) $this->total_budget) * 100, 2);
    }

    /**
     * Check if budget has sufficient funds
     */
    public function hasSufficientFunds(float $amount): bool
    {
        return $this->available_budget >= $amount;
    }

    /**
     * Format budget as Indonesian Rupiah
     */
    public function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
