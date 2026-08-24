<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewTaskReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'approve_with_remarks', 'reject', 'return_for_revision', 'request_more_information'])],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
