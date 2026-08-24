<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestDecidedNotification extends Notification
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
            'status' => $this->leaveRequest->status,
            'message' => "Your {$this->leaveRequest->leave_type} leave request ({$this->leaveRequest->start_date->toDateString()} to {$this->leaveRequest->end_date->toDateString()}) was {$this->leaveRequest->status}.",
        ];
    }
}
