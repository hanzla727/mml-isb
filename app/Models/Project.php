<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A Project belongs to a Department (Hospital Project, Mosque Construction,
 * Fundraising Campaign, ...) — Meetings and Tasks can optionally be linked
 * to one. Department is shared/global across every UC, so a Project also
 * carries its own uc_id directly (it's UC-specific work, even when its
 * Department isn't) — PP-level performance rolls Projects up through this
 * column, not through the Department.
 */
class Project extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'department_id',
        'uc_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'start_date', 'end_date', 'department_id', 'uc_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function uc(): BelongsTo
    {
        return $this->belongsTo(Uc::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ScheduledMeeting::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Percentage of this project's tasks that are completed/approved/closed.
     */
    public function progress(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }

        $done = $this->tasks()->whereIn('status', ['completed', 'approved', 'closed'])->count();

        return (int) round(($done / $total) * 100);
    }
}
