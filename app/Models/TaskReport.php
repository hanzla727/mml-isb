<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TaskReport extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'work_summary',
        'description',
        'achievements',
        'problems_faced',
        'next_plan',
        'working_hours',
        'amount_collected',
        'remarks',
        'review_status',
        'version',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_remarks',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'decimal:2',
            'amount_collected' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['review_status', 'version', 'reviewed_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
