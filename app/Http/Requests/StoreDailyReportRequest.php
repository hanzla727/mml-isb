<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_date' => [
                'required', 'date',
                Rule::unique('daily_reports', 'report_date')
                    ->where('user_id', $this->user()->id)
                    ->ignore($this->route('dailyReport')),
            ],
            'field_start_time' => ['required', 'date_format:H:i'],
            'field_end_time' => ['required', 'date_format:H:i', 'after:field_start_time'],
            'status' => ['required', Rule::in(['draft', 'submitted'])],
            'summary' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'tomorrow_plan' => ['nullable', 'string'],

            'meetings' => ['nullable', 'array'],
            'meetings.*.contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'meetings.*.name' => ['required_without:meetings.*.contact_id', 'string', 'max:255'],
            'meetings.*.phone' => ['required_without:meetings.*.contact_id', 'string', 'max:50'],
            'meetings.*.cnic' => ['nullable', 'string', 'max:50'],
            'meetings.*.address' => ['nullable', 'string', 'max:500'],
            'meetings.*.photo_path' => ['nullable', 'string'],
            'meetings.*.title' => ['nullable', 'string', 'max:255'],
            'meetings.*.meeting_datetime' => ['nullable', 'date'],
            'meetings.*.category' => [
                'required',
                Rule::in(['general', 'fund_discussion', 'family_visit', 'follow_up', 'project_discussion', 'other']),
            ],
            'meetings.*.discussion' => ['nullable', 'string'],
            'meetings.*.follow_up_required' => ['boolean'],
            'meetings.*.notes' => ['nullable', 'string'],
            'meetings.*.select_all_volunteers' => ['boolean'],
            'meetings.*.participant_ids' => ['nullable', 'array'],
            'meetings.*.participant_ids.*' => ['integer', 'exists:users,id'],

            'task_progress' => ['nullable', 'array'],
            'task_progress.*.target_id' => ['required', 'integer', 'exists:targets,id'],
            'task_progress.*.current_value' => ['nullable', 'numeric', 'min:0'],
            'task_progress.*.is_completed' => ['boolean'],
            'task_progress.*.notes' => ['nullable', 'string'],
        ];
    }
}
