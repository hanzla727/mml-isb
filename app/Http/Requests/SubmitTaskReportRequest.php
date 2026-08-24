<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTaskReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'achievements' => ['nullable', 'string'],
            'problems_faced' => ['nullable', 'string'],
            'next_plan' => ['nullable', 'string'],
            'working_hours' => ['nullable', 'numeric', 'min:0'],
            'amount_collected' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }
}
