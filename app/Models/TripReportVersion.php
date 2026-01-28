<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TripReportVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'trip_report_id',
        'version_number',
        'content',
        'changes_summary',
        'changed_by',
        'file_path',
    ];

    protected $casts = [
        'content' => 'array',
        'version_number' => 'integer',
    ];

    public function tripReport()
    {
        return $this->belongsTo(TripReport::class);
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Create a new version from current trip report state
     */
    public static function createFromReport(TripReport $report, string $changesSummary = null): self
    {
        $latestVersion = $report->versions()->max('version_number') ?? 0;

        return self::create([
            'trip_report_id' => $report->id,
            'version_number' => $latestVersion + 1,
            'content' => [
                'activities' => $report->activities,
                'outputs' => $report->outputs,
                'report_date' => $report->report_date,
                'reviewed_by' => $report->reviewed_by,
            ],
            'changes_summary' => $changesSummary ?? 'Auto-saved version',
            'changed_by' => auth()->id(),
            'file_path' => $report->file_path,
        ]);
    }

    /**
     * Compare with another version
     */
    public function compareWith(TripReportVersion $other): array
    {
        $differences = [];
        $thisContent = $this->content ?? [];
        $otherContent = $other->content ?? [];

        foreach ($thisContent as $key => $value) {
            if (!isset($otherContent[$key]) || $otherContent[$key] !== $value) {
                $differences[$key] = [
                    'old' => $otherContent[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $differences;
    }
}
