<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'cnic' => $this->cnic,
            'address' => $this->address,
            'photo_url' => $this->photo_path ? asset('storage/'.$this->photo_path) : null,
            'notes' => $this->notes,
            'na' => $this->whenLoaded('na', fn () => $this->na ? ['id' => $this->na->id, 'name' => $this->na->name] : null),
            'uc' => $this->whenLoaded('uc', fn () => $this->uc ? ['id' => $this->uc->id, 'name' => $this->uc->name] : null),
            'meetings_count' => $this->whenCounted('meetings'),
            'meetings' => MeetingResource::collection($this->whenLoaded('meetings')),
            'created_at' => $this->created_at,
        ];
    }
}
