<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'progress' => $this->progress(),
            'department' => $this->whenLoaded('department', fn () => $this->department ? ['id' => $this->department->id, 'name' => $this->department->name] : null),
            'uc' => $this->whenLoaded('uc', fn () => $this->uc ? ['id' => $this->uc->id, 'name' => $this->uc->name] : null),
            // Set by the controller — every volunteer (from this project's tasks
            // + meetings) grouped by their own Department.
            'departments' => $this->when(isset($this->department_members), fn () => $this->department_members),
            'my_tasks_count' => $this->when(isset($this->my_tasks_count), fn () => $this->my_tasks_count),
            'created_at' => $this->created_at,
        ];
    }
}
