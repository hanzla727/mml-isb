<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', Rule::in(['meeting_reminder', 'event', 'deadline', 'general'])],
            'audience_scope' => ['required', Rule::in(['all', 'department', 'user'])],
            'audience_id' => ['required_unless:audience_scope,all', 'nullable', 'integer'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
