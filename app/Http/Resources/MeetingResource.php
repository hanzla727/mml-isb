<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $viewerPivot = $viewer
            ? $this->participants->firstWhere('id', $viewer->id)?->pivot
            : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'meeting_datetime' => $this->meeting_datetime,
            'category' => $this->category,
            'discussion' => $this->discussion,
            'follow_up_required' => $this->follow_up_required,
            'notes' => $this->notes,
            'photo_url' => $this->photo_path ? asset('storage/'.$this->photo_path) : null,
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'daily_report_id' => $this->daily_report_id,
            'creator' => $this->whenLoaded('dailyReport', fn () => [
                'id' => $this->dailyReport->user->id,
                'name' => $this->dailyReport->user->name,
            ]),
            'participants' => $this->whenLoaded('participants', fn () => $this->participants->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'read_at' => $p->pivot->read_at,
            ])),
            'is_read' => $viewerPivot?->read_at !== null,
            'created_at' => $this->created_at,
        ];
    }
}
