<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduledMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'form_template_id' => ['nullable', 'integer', 'exists:form_templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'agenda' => ['nullable', 'string'],
            'organizer_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', Rule::in(['upcoming', 'ongoing', 'completed', 'cancelled'])],

            'scope' => ['required', Rule::in(['individual', 'department', 'departments', 'uc', 'na', 'nas', 'all', 'my_scope'])],
            'user_ids' => ['required_if:scope,individual', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'department_id' => ['required_if:scope,department', 'nullable', 'integer', 'exists:departments,id'],
            'department_ids' => ['required_if:scope,departments', 'nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'uc_id' => ['required_if:scope,uc', 'nullable', 'integer', 'exists:ucs,id'],
            'na_id' => ['required_if:scope,na', 'nullable', 'integer', 'exists:nas,id'],
            'na_ids' => ['required_if:scope,nas', 'nullable', 'array'],
            'na_ids.*' => ['integer', 'exists:nas,id'],

            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['required_if:is_recurring,1', Rule::in(['daily', 'weekly', 'monthly'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:52'],
            'recurrence_until' => ['nullable', 'date', 'after:meeting_date'],
        ];
    }
}
