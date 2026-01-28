<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transportation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sbm_setting_id',
        'type',
        'route',
        'max_amount',
        'is_riil',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'is_riil' => 'boolean',
    ];

    public function sbmSetting(): BelongsTo
    {
        return $this->belongsTo(SbmSetting::class);
    }
}
