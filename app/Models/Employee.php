<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'user_id',
        'nip',
        'name',
        'birth_date',
        'email',
        'phone',
        'photo',
        'position',
        'rank',
        'grade',
        'employment_status',
        'approval_level',
        'superior_nip',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spds(): HasMany
    {
        return $this->hasMany(Spd::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    public function tripReports(): HasMany
    {
        return $this->hasMany(TripReport::class);
    }

    public function headOfUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'head_employee_id');
    }

    /**
     * Get the employee's initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials;
    }

    /**
     * LOGIC MAP: Get human-readable level name from approval_level (1-6)
     * 1 = Staff/Dosen
     * 2 = Kepala Prodi
     * 3 = Wakil Dekan
     * 4 = Dekan
     * 5 = Wakil Rektor
     * 6 = Rektor
     */
    public function getLevelNameAttribute(): string
    {
        return match ($this->approval_level) {
            1 => 'Staff/Dosen',
            2 => 'Kepala Prodi',
            3 => 'Wakil Dekan',
            4 => 'Dekan',
            5 => 'Wakil Rektor',
            6 => 'Rektor',
            default => 'Unknown',
        };
    }

    /**
     * Check if employee is an approver
     */
    public function isApprover(): bool
    {
        return $this->headOfUnits()->exists() ||
            ($this->user && $this->user->role === 'approver');
    }
}
