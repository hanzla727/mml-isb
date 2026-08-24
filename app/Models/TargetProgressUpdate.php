<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetProgressUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_id',
        'user_id',
        'period_key',
        'current_value',
        'notes',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'decimal:2',
            'is_completed' => 'boolean',
        ];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
