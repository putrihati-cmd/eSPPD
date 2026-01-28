<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accommodation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sbm_setting_id',
        'province',
        'grade_level',
        'max_amount',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
    ];

    public function sbmSetting(): BelongsTo
    {
        return $this->belongsTo(SbmSetting::class);
    }

    /**
     * Get accommodation rate for province and grade
     */
    public static function getRate(string $sbmSettingId, string $province, string $gradeLevel): ?float
    {
        $accommodation = static::where('sbm_setting_id', $sbmSettingId)
            ->where('province', $province)
            ->where('grade_level', $gradeLevel)
            ->first();

        return $accommodation ? (float) $accommodation->max_amount : null;
    }

    /**
     * Map employee grade to grade level
     */
    public static function mapGradeToLevel(string $grade): string
    {
        // Extract roman numeral from grade (e.g., "III/b" -> "III")
        $roman = strtoupper(explode('/', $grade)[0] ?? 'III');
        
        return match ($roman) {
            'IV' => 'golongan_IV',
            'III' => 'golongan_III',
            'II' => 'golongan_II',
            'I' => 'golongan_I',
            default => 'golongan_III',
        };
    }
}
