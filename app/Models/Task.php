<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Task extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const OPEN_STATUSES = [
        'not_assigned', 'assigned', 'accepted', 'in_progress', 'waiting_for_information',
        'needs_revision',
    ];

    protected $fillable = [
        'scheduled_meeting_id',
        'project_id',
        'form_template_id',
        'title',
        'description',
        'priority',
        'due_date',
        'due_time',
        'status',
        'notes',
        'created_by',
        'recurrence_rule',
        'recurring_parent_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'recurrence_rule' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'due_date'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function scheduledMeeting(): BelongsTo
    {
        return $this->belongsTo(ScheduledMeeting::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function formSubmissions(): MorphMany
    {
        return $this->morphMany(FormSubmission::class, 'submittable');
    }

    public function recurringParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurring_parent_id');
    }

    public function recurrences(): HasMany
    {
        return $this->hasMany(self::class, 'recurring_parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(TaskReport::class)->orderBy('version');
    }

    public function latestReport(): HasOne
    {
        return $this->hasOne(TaskReport::class)->latestOfMany('version');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function isAssignee(User $user): bool
    {
        return $this->assignees()->where('user_id', $user->id)->exists();
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'approved', 'closed', 'cancelled'], true);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->whereNotIn('status', ['completed', 'approved', 'closed', 'cancelled']);
    }
}
