<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripOutput extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'report_id',
        'description',
        'order',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(TripReport::class, 'report_id');
    }
}
