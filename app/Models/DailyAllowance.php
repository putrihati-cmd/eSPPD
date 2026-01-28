<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAllowance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sbm_setting_id',
        'province',
        'category',
        'amount',
        'representation_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'representation_amount' => 'decimal:2',
    ];

    public function sbmSetting(): BelongsTo
    {
        return $this->belongsTo(SbmSetting::class);
    }

    /**
     * Get rate for a specific province and category
     */
    public static function getRate(string $sbmSettingId, string $province, string $category = 'luar_kota'): ?float
    {
        $allowance = static::where('sbm_setting_id', $sbmSettingId)
            ->where('province', $province)
            ->where('category', $category)
            ->first();

        return $allowance ? (float) $allowance->amount : null;
    }
}
