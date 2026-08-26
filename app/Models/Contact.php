<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Contact extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'cnic',
        'address',
        'photo_path',
        'notes',
        'created_by',
        'na_id',
        'uc_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'cnic', 'address', 'na_id', 'uc_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function na(): BelongsTo
    {
        return $this->belongsTo(Na::class);
    }

    public function uc(): BelongsTo
    {
        return $this->belongsTo(Uc::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
