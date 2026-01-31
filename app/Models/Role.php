<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model - RBAC.md Implementation
 * 
 * Hierarchy Levels:
 * - 99: superadmin
 * - 98: admin
 * - 6: rektor
 * - 5: warek (Wakil Rektor)
 * - 4: dekan
 * - 3: wadek (Wakil Dekan)
 * - 2: kabag/kaprodi
 * - 1: dosen/pegawai
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'level',
        'description',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    /**
     * Get users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if this role can approve SPD
     */
    public function canApprove(): bool
    {
        return $this->level >= 2; // Kaprodi ke atas
    }

    /**
     * Check if this role can view all SPD in faculty
     */
    public function canViewAllFaculty(): bool
    {
        return $this->level >= 3; // Wadek ke atas
    }

    /**
     * Check if this role can view all SPD institution-wide
     */
    public function canViewAllInstitution(): bool
    {
        return $this->level >= 5 || in_array($this->name, ['superadmin', 'admin']);
    }

    /**
     * Check if this role is admin level
     */
    public function isAdmin(): bool
    {
        return in_array($this->name, ['superadmin', 'admin']);
    }

    /**
     * Check if this role is executive (Dekan+)
     */
    public function isExecutive(): bool
    {
        return $this->level >= 4;
    }

    /**
     * Check if this role can override/force actions
     */
    public function canOverride(): bool
    {
        return $this->level >= 4 || in_array($this->name, ['superadmin', 'admin']);
    }

    /**
     * Check if this role can delegate
     */
    public function canDelegate(): bool
    {
        return $this->level >= 3; // Wadek ke atas
    }

    /**
     * Get role by name
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Get approval limit based on role level
     */
    public function getApprovalLimit(): ?int
    {
        return match($this->level) {
            3 => 10000000,   // Wadek: 10jt
            4 => 50000000,   // Dekan: 50jt
            5 => 100000000,  // Warek: 100jt
            6 => null,       // Rektor: unlimited
            default => null,
        };
    }
}
