<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Team extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'department_id',
        'uc_id',
        'leader_id',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'department_id', 'uc_id', 'leader_id', 'is_active'])
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

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
