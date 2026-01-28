<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Spd extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'employee_id',
        'spt_number',
        'spd_number',
        'destination',
        'purpose',
        'invitation_number',
        'invitation_file',
        'departure_date',
        'return_date',
        'duration',
        'budget_id',
        'estimated_cost',
        'actual_cost',
        'transport_type',
        'needs_accommodation',
        'status',
        'created_by',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'needs_accommodation' => 'boolean',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(TripReport::class);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'completed' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            default => 'Unknown',
        };
    }

    /**
     * Format cost as Indonesian Rupiah
     */
    public function formatCost(?float $amount = null): string
    {
        $value = $amount ?? $this->estimated_cost;
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    /**
     * Check if SPD can be edited
     */
    public function canEdit(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if SPD can be submitted
     */
    public function canSubmit(): bool
    {
        return $this->status === 'draft' && $this->costs()->exists();
    }

    /**
     * Check if SPD needs report
     */
    public function needsReport(): bool
    {
        return $this->status === 'approved' && !$this->report()->exists();
    }

    /**
     * Get pending approval for current SPD
     */
    public function getPendingApproval(): ?Approval
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->orderBy('level')
            ->first();
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for current month SPDs
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
