<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type', // trip_report, sppd, spt
        'file_path',
        'is_default',
        'uploaded_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function getDefaultTemplate(string $type): ?self
    {
        return self::ofType($type)->default()->first();
    }
}
