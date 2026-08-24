<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolunteerDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'document_type' => $this->document_type,
            'file_url' => $this->whenLoaded('file', fn () => $this->file ? asset('storage/'.$this->file->path) : null),
            'mime_type' => $this->whenLoaded('file', fn () => $this->file?->mime_type),
            'uploader' => $this->whenLoaded('uploader', fn () => $this->uploader ? ['id' => $this->uploader->id, 'name' => $this->uploader->name] : null),
            'created_at' => $this->created_at,
        ];
    }
}
