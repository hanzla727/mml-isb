<?php

namespace App\Notifications;

use App\Models\ExpenseClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseClaimDecidedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ExpenseClaim $expenseClaim)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'expense_claim_id' => $this->expenseClaim->id,
            'status' => $this->expenseClaim->status,
            'message' => "Your {$this->expenseClaim->expense_type} expense claim for {$this->expenseClaim->amount} was {$this->expenseClaim->status}.",
        ];
    }
}
