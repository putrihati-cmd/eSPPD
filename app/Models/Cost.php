<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cost extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'spd_id',
        'category',
        'description',
        'estimated_amount',
        'actual_amount',
        'receipt_file',
        'receipt_number',
        'receipt_date',
        'sbm_max_amount',
        'exceeds_sbm',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'sbm_max_amount' => 'decimal:2',
        'receipt_date' => 'date',
        'exceeds_sbm' => 'boolean',
    ];

    public function spd(): BelongsTo
    {
        return $this->belongsTo(Spd::class);
    }

    /**
     * Get category label in Indonesian
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'uang_harian' => 'Uang Harian',
            'penginapan' => 'Penginapan',
            'transport' => 'Transportasi',
            'representasi' => 'Uang Representasi',
            'lainnya' => 'Biaya Lainnya',
            default => 'Lainnya',
        };
    }

    /**
     * Format amount as Indonesian Rupiah
     */
    public function formatAmount(?float $amount = null): string
    {
        $value = $amount ?? $this->estimated_amount;
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}
