<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->category,
            'audience_scope' => $this->audience_scope,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'is_read' => (bool) ($this->is_read ?? false),
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}
