<?php

namespace App\Notifications;

use App\Models\ExpenseClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpenseClaimSubmittedNotification extends Notification
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
            'user_id' => $this->expenseClaim->user_id,
            'message' => "{$this->expenseClaim->user->name} submitted a {$this->expenseClaim->expense_type} expense claim for {$this->expenseClaim->amount}.",
        ];
    }
}
