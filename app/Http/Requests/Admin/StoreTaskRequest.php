<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_meeting_id' => ['nullable', 'integer', 'exists:scheduled_meetings,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'form_template_id' => ['nullable', 'integer', 'exists:form_templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'due_date' => ['nullable', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],

            'scope' => ['required', Rule::in(['individual', 'team', 'teams', 'department', 'departments', 'uc', 'na', 'nas', 'all'])],
            'user_ids' => ['required_if:scope,individual', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'department_id' => ['required_if:scope,department', 'nullable', 'integer', 'exists:departments,id'],
            'department_ids' => ['required_if:scope,departments', 'nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'team_id' => ['required_if:scope,team', 'nullable', 'integer', 'exists:teams,id'],
            'team_ids' => ['required_if:scope,teams', 'nullable', 'array'],
            'team_ids.*' => ['integer', 'exists:teams,id'],
            'uc_id' => ['required_if:scope,uc', 'nullable', 'integer', 'exists:ucs,id'],
            'na_id' => ['required_if:scope,na', 'nullable', 'integer', 'exists:nas,id'],
            'na_ids' => ['required_if:scope,nas', 'nullable', 'array'],
            'na_ids.*' => ['integer', 'exists:nas,id'],

            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['required_if:is_recurring,1', Rule::in(['daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:52'],
            'recurrence_until' => ['nullable', 'date', 'after:due_date'],
        ];
    }
}
