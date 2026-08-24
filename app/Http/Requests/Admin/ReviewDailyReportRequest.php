<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $report = $this->route('dailyReport');
        $isTeamLeaderStage = $report && $report->review_status === 'pending_review';

        return [
            'decision' => [
                'required',
                $isTeamLeaderStage
                    ? Rule::in(['recommend_approve', 'needs_revision'])
                    : Rule::in(['approve', 'approve_with_remarks', 'reject', 'needs_revision', 'close']),
            ],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
