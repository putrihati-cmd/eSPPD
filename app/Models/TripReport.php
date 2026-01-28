<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'spd_id',
        'employee_id',
        'actual_departure_date',
        'actual_return_date',
        'actual_duration',
        'attachments',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'submitted_at',
    ];

    protected $casts = [
        'actual_departure_date' => 'date',
        'actual_return_date' => 'date',
        'attachments' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function spd(): BelongsTo
    {
        return $this->belongsTo(Spd::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TripActivity::class, 'report_id')->orderBy('order');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(TripOutput::class, 'report_id')->orderBy('order');
    }

    /**
     * Check if report is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Get verification status label
     */
    public function getVerificationStatusAttribute(): string
    {
        if ($this->is_verified) {
            return 'Terverifikasi';
        }
        return $this->submitted_at ? 'Menunggu Verifikasi' : 'Draft';
    }
}
