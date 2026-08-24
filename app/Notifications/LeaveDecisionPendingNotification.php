<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveDecisionPendingNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => "{$this->leaveRequest->user->name}'s {$this->leaveRequest->leave_type} leave request has been pending for over 24 hours.",
        ];
    }
}
