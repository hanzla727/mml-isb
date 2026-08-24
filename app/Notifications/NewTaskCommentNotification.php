<?php

namespace App\Notifications;

use App\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTaskCommentNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TaskComment $comment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'task_id' => $this->comment->task_id,
            'task_comment_id' => $this->comment->id,
            'author' => $this->comment->user->name,
            'message' => "{$this->comment->user->name} commented on a task you're involved in.",
        ];
    }
}
