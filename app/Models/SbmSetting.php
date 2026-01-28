<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SbmSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'year',
        'pmk_number',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function dailyAllowances(): HasMany
    {
        return $this->hasMany(DailyAllowance::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    public function transportations(): HasMany
    {
        return $this->hasMany(Transportation::class);
    }

    /**
     * Get active SBM setting for a given year
     */
    public static function getActive(int $year = null): ?self
    {
        $year = $year ?? now()->year;
        return static::where('year', $year)
            ->where('is_active', true)
            ->first();
    }
}
