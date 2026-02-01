<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Spd extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
        'travel_type',           // dari ceking.md: dalam_kota/luar_kota/luar_negeri
        'needs_accommodation',
        'status',
        'current_approver_nip',  // dari ceking.md: tracking siapa yang approve saat ini
        'rejection_reason',      // dari ceking.md: alasan ditolak
        'approved_at',           // dari ceking.md: timestamp final approval
        'approved_by',           // dari ceking.md: siapa yang final approve
        'created_by',
        'submitted_at',
        'completed_at',
        'deleted_by',            // dari fitur.md: siapa yang hapus (NIP)
        'deleted_reason',        // dari fitur.md: alasan dihapus
        // Revision fields (dari fitur.md)
        'revision_count',
        'revision_history',
        'rejected_at',
        'rejected_by',
        'previous_approver_nip',
        'spt_generated_at',
        'spd_generated_at',
        'spt_file_path',
        'spd_file_path',
    ];

    public function followers(): HasMany
    {
        return $this->hasMany(SpdFollower::class);
    }

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'needs_accommodation' => 'boolean',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
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
     * Get the employee who approved this SPD (from ceking.md)
     */
    public function approvedByEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get required approval level based on travel type (from ceking.md)
     * luar_negeri: level 5 (WR)
     * luar_kota: level 4 (Dekan)
     * dalam_kota: level 3 (Wadek)
     */
    public function getRequiredLevel(): int
    {
        return match($this->travel_type ?? 'dalam_kota') {
            'luar_negeri' => 5,
            'luar_kota' => 4,
            default => 3,
        };
    }

    /**
     * Check if this SPD can be finally approved by the given level
     */
    public function canBeFinallyApprovedBy(int $approverLevel): bool
    {
        return $approverLevel >= $this->getRequiredLevel();
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

    /**
     * State Machine Pattern (from md/2.md)
     */
    public function transitionTo(string $newState, string $actorNip): void
    {
        $allowed = match($this->status) {
            'draft' => ['submitted', 'deleted'],
            'submitted' => ['approved', 'rejected', 'cancelled'],
            'approved' => ['completed', 'cancelled'],
            'rejected' => ['draft', 'deleted'],
            default => []
        };
        
        if (!in_array($newState, $allowed)) {
            Log::warning("Invalid state transition attempted: {$this->status} -> {$newState}", [
                'spd_id' => $this->id,
                'actor' => $actorNip
            ]);
            // For now, we allow it but log it, or throw exception if strict
            // throw new \Exception("Invalid state transition");
        }
        
        $this->update([
            'status' => $newState,
            'current_approver_nip' => $newState === 'approved' ? null : $this->current_approver_nip,
        ]);
        
        // Emit logic could go here
    }
}

