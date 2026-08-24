<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'user' => $this->whenLoaded('user', fn () => ['id' => $this->user->id, 'name' => $this->user->name]),
            'work_summary' => $this->work_summary,
            'description' => $this->description,
            'achievements' => $this->achievements,
            'problems_faced' => $this->problems_faced,
            'next_plan' => $this->next_plan,
            'working_hours' => $this->working_hours,
            'remarks' => $this->remarks,
            'review_status' => $this->review_status,
            'version' => $this->version,
            'submitted_at' => $this->submitted_at,
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? ['id' => $this->reviewer->id, 'name' => $this->reviewer->name] : null),
            'reviewed_at' => $this->reviewed_at,
            'review_remarks' => $this->review_remarks,
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($m) => [
                'id' => $m->id,
                'url' => asset("storage/{$m->path}"),
                'mime_type' => $m->mime_type,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
