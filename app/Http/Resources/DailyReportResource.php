<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'report_date' => $this->report_date->toDateString(),
            'field_start_time' => $this->field_start_time,
            'field_end_time' => $this->field_end_time,
            'total_hours' => $this->total_hours,
            'summary' => $this->summary,
            'challenges' => $this->challenges,
            'tomorrow_plan' => $this->tomorrow_plan,
            'status' => $this->status,
            'review_status' => $this->review_status,
            'admin_remarks' => $this->admin_remarks,
            'meetings' => MeetingResource::collection($this->whenLoaded('meetings')),
            'meetings_count' => $this->whenCounted('meetings'),
            'created_at' => $this->created_at,
        ];
    }
}
