<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpdFollower extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'spd_id',
        'employee_id',
    ];

    public function spd(): BelongsTo
    {
        return $this->belongsTo(Spd::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
