<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'metric',
        'target_value',
        'scope',
        'scope_id',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(TargetProgressUpdate::class);
    }

    /**
     * Resolve the users this target currently applies to based on its scope.
     */
    public function assignedUsers()
    {
        return match ($this->scope) {
            'all' => User::query(),
            'na' => User::query()->where('na_id', $this->scope_id),
            'uc' => User::query()->where('uc_id', $this->scope_id),
            'department' => User::query()->where('department_id', $this->scope_id),
            'user' => User::query()->where('id', $this->scope_id),
        };
    }

    /**
     * Scope to targets currently applicable to the given user (the inverse of assignedUsers()).
     */
    public function scopeApplicableTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('scope', 'all')
                ->orWhere(fn ($q) => $q->where('scope', 'na')->where('scope_id', $user->na_id))
                ->orWhere(fn ($q) => $q->where('scope', 'uc')->where('scope_id', $user->uc_id))
                ->orWhere(fn ($q) => $q->where('scope', 'department')->where('scope_id', $user->department_id))
                ->orWhere(fn ($q) => $q->where('scope', 'user')->where('scope_id', $user->id));
        });
    }
}
