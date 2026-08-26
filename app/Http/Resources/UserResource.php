<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_path ? asset('storage/'.$this->avatar_path) : null,
            'role' => $this->getRoleNames()->first(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'uc' => $this->whenLoaded('uc', fn () => $this->uc ? [
                'id' => $this->uc->id,
                'name' => $this->uc->name,
                'na' => $this->uc->relationLoaded('na') && $this->uc->na
                    ? ['id' => $this->uc->na->id, 'name' => $this->uc->na->name]
                    : null,
            ] : null),
            'na' => $this->whenLoaded('na', fn () => $this->na ? [
                'id' => $this->na->id,
                'name' => $this->na->name,
            ] : null),
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
        ];
    }
}
