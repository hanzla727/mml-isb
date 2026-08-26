<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DailyReport extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'report_date',
        'field_start_time',
        'field_end_time',
        'total_hours',
        'summary',
        'challenges',
        'tomorrow_plan',
        'status',
        'review_status',
        'admin_reviewed_by',
        'admin_reviewed_at',
        'admin_remarks',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'total_hours' => 'decimal:2',
            'admin_reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DailyReport $report) {
            if ($report->field_start_time && $report->field_end_time) {
                $start = Carbon::parse($report->field_start_time);
                $end = Carbon::parse($report->field_end_time);
                $report->total_hours = round($start->diffInMinutes($end) / 60, 2);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'review_status', 'admin_reviewed_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function adminReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_reviewed_by');
    }
}
