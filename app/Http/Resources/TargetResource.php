<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentValue = (float) ($this->current_value ?? 0);
        $targetValue = (float) $this->target_value;
        $percentage = $targetValue > 0 ? min(100, round(($currentValue / $targetValue) * 100, 1)) : 0;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'metric' => $this->metric,
            'target_value' => $targetValue,
            'scope' => $this->scope,
            'scope_id' => $this->scope_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_active' => $this->is_active,
            'progress' => [
                'current_value' => $currentValue,
                'percentage' => $percentage,
                'remaining' => max(0, $targetValue - $currentValue),
                'status' => $percentage >= 100 ? 'completed' : 'in_progress',
            ],
            'created_at' => $this->created_at,
        ];
    }
}
