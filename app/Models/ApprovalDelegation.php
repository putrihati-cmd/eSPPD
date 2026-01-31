<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegator_id',
        'delegate_id',
        'reason',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }

    /**
     * Check if delegation is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get active delegations for a delegator
     */
    public static function getActiveDelegations(User $delegator)
    {
        return self::where('delegator_id', $delegator->id)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            })
            ->get();
    }
}
