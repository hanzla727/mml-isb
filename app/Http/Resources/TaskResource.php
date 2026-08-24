<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'due_time' => $this->due_time,
            'status' => $this->status,
            'is_overdue' => $this->isOverdue(),
            'notes' => $this->notes,
            'scheduled_meeting' => $this->whenLoaded('scheduledMeeting', fn () => $this->scheduledMeeting ? [
                'id' => $this->scheduledMeeting->id,
                'title' => $this->scheduledMeeting->title,
            ] : null),
            'assignees' => $this->whenLoaded('assignees', fn () => $this->assignees->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ])),
            'latest_report' => $this->whenLoaded('latestReport', fn () => $this->latestReport ? new TaskReportResource($this->latestReport) : null),
            'created_at' => $this->created_at,
        ];
    }
}
