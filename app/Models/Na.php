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

/**
 * NA (National Assembly constituency) — the unit a person is actually
 * assigned to manage (its "NA Head"). Islamabad Capital Territory has no
 * Provincial Assembly, so NA is the top real level here; every UC under an
 * NA is that person's responsibility.
 */
class Na extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'na_head_id',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'na_head_id', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function naHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'na_head_id');
    }

    public function ucs(): HasMany
    {
        return $this->hasMany(Uc::class);
    }

    /**
     * Every volunteer/team-leader/NA-head directly assigned to this NA
     * (User::na_id, denormalized alongside their uc_id) — not the Admins
     * who merely have access to it.
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Admins who can access this NA — a many-to-many, unlike NA Head which
     * is a single owner (na_head_id).
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_na');
    }
}
