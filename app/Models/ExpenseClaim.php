<?php

namespace App\Models;

use App\Models\Concerns\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ExpenseClaim extends Model
{
    use HasApprovalWorkflow, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'expense_type',
        'amount',
        'date',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['expense_type', 'amount', 'date', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * A photo of the physical receipt — required at submission time (see
     * StoreExpenseClaimRequest) so reviewers have proof before approving.
     */
    public function receipt(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable');
    }
}
