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
 * UC (Union Council) — the bottom-most operational unit. Projects and
 * Volunteers attach here. "sector" (e.g. F-10, G-9) is kept as a purely
 * optional, informal label — not a structural level of its own.
 */
class Uc extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'na_id',
        'name',
        'sector',
        'description',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'na_id', 'sector', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function na(): BelongsTo
    {
        return $this->belongsTo(Na::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
