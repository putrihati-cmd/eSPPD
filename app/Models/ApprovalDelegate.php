<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApprovalDelegate extends Model
{
    use HasUuids;

    protected $fillable = [
        'delegator_id',
        'delegate_id',
        'start_date',
        'end_date',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function delegator()
    {
        return $this->belongsTo(Employee::class, 'delegator_id');
    }

    public function delegate()
    {
        return $this->belongsTo(Employee::class, 'delegate_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public static function getDelegateFor($employeeId)
    {
        return self::active()
            ->where('delegator_id', $employeeId)
            ->first()?->delegate;
    }
}
