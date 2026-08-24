<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'metric' => ['required', Rule::in(['hours', 'meetings', 'custom'])],
            'target_value' => ['required', 'numeric', 'min:0'],
            'scope' => ['required', Rule::in(['all', 'department', 'team', 'user'])],
            'scope_id' => ['required_unless:scope,all', 'nullable', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ];
    }
}
