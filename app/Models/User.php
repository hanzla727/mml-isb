<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'password',
        'pin',
        'avatar_path',
        'na_id',
        'uc_id',
        'department_id',
        'reporting_head_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'na_id', 'uc_id', 'department_id', 'reporting_head_id', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function na(): BelongsTo
    {
        return $this->belongsTo(Na::class);
    }

    public function uc(): BelongsTo
    {
        return $this->belongsTo(Uc::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * The generalized "who this person answers to" pointer — explicit
     * rather than derived from uc()/na() so it can be freely overridden
     * (e.g. a volunteer with no obvious head yet, or a direct exception).
     */
    public function reportingHead(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_head_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'reporting_head_id');
    }

    /**
     * The NA this user heads (if they hold the na_head role for it),
     * distinct from na() which is the NA they're a member of.
     */
    public function naLed(): HasOne
    {
        return $this->hasOne(Na::class, 'na_head_id');
    }

    /**
     * NAs this Admin has been granted access to — plural, unlike NA Head
     * who owns exactly one NA via naLed().
     */
    public function adminNas(): BelongsToMany
    {
        return $this->belongsToMany(Na::class, 'admin_na');
    }

    /**
     * UCs this user heads (if they hold the uc_head role) — a UC Head sits
     * between NA Head and Team Leader, responsible for one or more UCs
     * (every department/team under each), unlike a plain volunteer's
     * single uc_id membership.
     */
    public function ucsHeaded(): BelongsToMany
    {
        return $this->belongsToMany(Uc::class, 'uc_heads');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function contactsCreated(): HasMany
    {
        return $this->hasMany(Contact::class, 'created_by');
    }

    public function targetsCreated(): HasMany
    {
        return $this->hasMany(Target::class, 'created_by');
    }

    public function targetProgressUpdates(): HasMany
    {
        return $this->hasMany(TargetProgressUpdate::class);
    }

    public function announcementsCreated(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function meetingsParticipating(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'meeting_participants')
            ->withPivot(['notified_at', 'read_at'])
            ->withTimestamps();
    }

    public function scheduledMeetingsOrganized(): HasMany
    {
        return $this->hasMany(ScheduledMeeting::class, 'organizer_id');
    }

    public function scheduledMeetingsParticipating(): BelongsToMany
    {
        return $this->belongsToMany(ScheduledMeeting::class, 'scheduled_meeting_participants')
            ->withPivot(['notified_at', 'read_at'])
            ->withTimestamps();
    }

    public function tasksAssigned(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees')->withTimestamps();
    }

    public function taskReports(): HasMany
    {
        return $this->hasMany(TaskReport::class);
    }

    public function meetingAttendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VolunteerDocument::class);
    }
}
