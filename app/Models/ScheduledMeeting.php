<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ScheduledMeeting extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'project_id',
        'form_template_id',
        'title',
        'type',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'description',
        'agenda',
        'organizer_id',
        'status',
        'created_by',
        'recurrence_rule',
        'recurring_parent_id',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
            'recurrence_rule' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'meeting_date', 'status', 'organizer_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
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

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'scheduled_meeting_participants')
            ->withPivot(['notified_at', 'read_at'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }
}
