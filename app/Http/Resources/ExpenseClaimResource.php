<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseClaimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_type' => $this->expense_type,
            'amount' => (float) $this->amount,
            'date' => $this->date?->toDateString(),
            'description' => $this->description,
            'status' => $this->status,
            'receipt_url' => $this->whenLoaded('receipt', fn () => $this->receipt ? asset('storage/'.$this->receipt->path) : null),
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? ['id' => $this->reviewer->id, 'name' => $this->reviewer->name] : null),
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
        ];
    }
}
