<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'category',
        'audience_scope',
        'audience_id',
        'created_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Resolve the users this announcement's audience currently applies to.
     */
    public function audienceUsers()
    {
        return match ($this->audience_scope) {
            'all' => User::query(),
            'na' => User::query()->where('na_id', $this->audience_id),
            'uc' => User::query()->where('uc_id', $this->audience_id),
            'department' => User::query()->where('department_id', $this->audience_id),
            'user' => User::query()->where('id', $this->audience_id),
        };
    }

    /**
     * Scope to announcements currently applicable to the given user (the inverse of audienceUsers()).
     */
    public function scopeApplicableTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('audience_scope', 'all')
                ->orWhere(fn ($q) => $q->where('audience_scope', 'na')->where('audience_id', $user->na_id))
                ->orWhere(fn ($q) => $q->where('audience_scope', 'uc')->where('audience_id', $user->uc_id))
                ->orWhere(fn ($q) => $q->where('audience_scope', 'department')->where('audience_id', $user->department_id))
                ->orWhere(fn ($q) => $q->where('audience_scope', 'user')->where('audience_id', $user->id));
        });
    }
}
