<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'contact_id',
        'title',
        'meeting_datetime',
        'category',
        'discussion',
        'follow_up_required',
        'notes',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_required' => 'boolean',
            'meeting_datetime' => 'datetime',
        ];
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_participants')
            ->withPivot(['notified_at', 'read_at'])
            ->withTimestamps();
    }

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }
}
