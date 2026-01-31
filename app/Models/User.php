<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'organization_id',
        'employee_id',
        'role',
        'role_id',      // RBAC.md: FK to roles table
        'permissions',  // RBAC.md: Custom permissions JSON
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * RBAC.md: Role relationship
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get permissions for this user
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Get role relationship (alias)
     */
    public function role(): BelongsTo
    {
        return $this->roleModel();
    }

    /**
     * RBAC.md: Get role level (1-99)
     */
    public function getRoleLevelAttribute(): int
    {
        return $this->roleModel?->level ?? 1;
    }

    /**
     * Check if user has a specific role by name
     */
    public function hasRole(string $role): bool
    {
        // Check both legacy 'role' column and new 'roleModel'
        if ($this->role === $role) {
            return true;
        }
        return $this->roleModel?->name === $role;
    }

    /**
     * Check if user has minimum level
     */
    public function hasMinLevel(int $level): bool
    {
        return $this->role_level >= $level;
    }

    /**
     * RBAC.md: Admin check (level 98+)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' ||
               $this->roleModel?->isAdmin() ?? false;
    }

    /**
     * RBAC.md: Can approve SPD (level >= 2)
     */
    public function isApprover(): bool
    {
        return $this->role === 'approver' ||
               $this->role_level >= 2 ||
               $this->isAdmin();
    }

    /**
     * RBAC.md: Finance role
     */
    public function isFinance(): bool
    {
        return $this->role === 'finance' || $this->isAdmin();
    }

    /**
     * RBAC.md: Executive level (Dekan+)
     */
    public function isExecutive(): bool
    {
        return $this->role_level >= 4;
    }

    /**
     * RBAC.md: Can override/force cancel
     */
    public function canOverride(): bool
    {
        return $this->roleModel?->canOverride() ?? $this->isAdmin();
    }

    /**
     * RBAC.md: Can delegate approval
     */
    public function canDelegate(): bool
    {
        return $this->roleModel?->canDelegate() ?? false;
    }

    /**
     * Get user initials for avatar
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
}
