<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledMeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $viewerPivot = $viewer && $this->relationLoaded('participants')
            ? $this->participants->firstWhere('id', $viewer->id)?->pivot
            : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'meeting_date' => $this->meeting_date->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'location' => $this->location,
            'description' => $this->description,
            'agenda' => $this->agenda,
            'status' => $this->status,
            'organizer' => $this->whenLoaded('organizer', fn () => [
                'id' => $this->organizer->id,
                'name' => $this->organizer->name,
            ]),
            'participants_count' => $this->whenCounted('participants'),
            'tasks_count' => $this->whenCounted('tasks'),
            'is_read' => $viewerPivot?->read_at !== null,
            'created_at' => $this->created_at,
        ];
    }
}
