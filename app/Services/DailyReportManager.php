<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\DailyReport;
use App\Models\Target;
use App\Models\User;
use App\Notifications\MeetingAssignedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DailyReportManager
{
    public function __construct(
        private readonly TargetProgressUpdater $progressUpdater,
        private readonly ReportApprovalService $approvalService,
    ) {
    }

    public function create(User $user, array $validated): DailyReport
    {
        $report = DB::transaction(function () use ($user, $validated) {
            $report = $user->dailyReports()->create([
                'report_date' => $validated['report_date'],
                'field_start_time' => $validated['field_start_time'],
                'field_end_time' => $validated['field_end_time'],
                'summary' => $validated['summary'] ?? null,
                'challenges' => $validated['challenges'] ?? null,
                'tomorrow_plan' => $validated['tomorrow_plan'] ?? null,
                'status' => $validated['status'],
            ]);

            $this->syncMeetings($report, $validated['meetings'] ?? [], $user);
            $this->syncTaskProgress($user, $report, $validated['task_progress'] ?? []);

            return $report;
        });

        $this->progressUpdater->syncForUser($user, Carbon::parse($report->report_date));

        if ($report->status === 'submitted') {
            $this->approvalService->submit($report);
        }

        return $report->load(['meetings.contact', 'user']);
    }

    public function update(DailyReport $report, array $validated): DailyReport
    {
        $wasNeedingRevision = $report->review_status === 'needs_revision';

        DB::transaction(function () use ($report, $validated) {
            $report->update([
                'field_start_time' => $validated['field_start_time'],
                'field_end_time' => $validated['field_end_time'],
                'summary' => $validated['summary'] ?? null,
                'challenges' => $validated['challenges'] ?? null,
                'tomorrow_plan' => $validated['tomorrow_plan'] ?? null,
                'status' => $validated['status'],
            ]);

            $report->meetings()->delete();
            $this->syncMeetings($report, $validated['meetings'] ?? [], $report->user);
            $this->syncTaskProgress($report->user, $report, $validated['task_progress'] ?? []);
        });

        $this->progressUpdater->syncForUser($report->user, Carbon::parse($report->report_date));

        if ($report->status === 'submitted') {
            if ($wasNeedingRevision) {
                $this->approvalService->resubmit($report);
            } elseif ($report->review_status === null) {
                $this->approvalService->submit($report);
            }
        }

        return $report->load(['meetings.contact', 'user']);
    }

    private function syncMeetings(DailyReport $report, array $meetings, User $creator): void
    {
        foreach ($meetings as $meetingData) {
            $contact = isset($meetingData['contact_id'])
                ? Contact::findOrFail($meetingData['contact_id'])
                : Contact::firstOrCreate(
                    ['phone' => $meetingData['phone']],
                    [
                        'name' => $meetingData['name'],
                        'cnic' => $meetingData['cnic'] ?? null,
                        'address' => $meetingData['address'] ?? null,
                        'created_by' => $creator->id,
                        'na_id' => $creator->na_id,
                        'uc_id' => $creator->uc_id,
                    ]
                );

            $meeting = $report->meetings()->create([
                'contact_id' => $contact->id,
                'title' => $meetingData['title'] ?? null,
                'meeting_datetime' => $meetingData['meeting_datetime']
                    ?? Carbon::parse($report->report_date)->setTimeFrom(now()),
                'category' => $meetingData['category'],
                'discussion' => $meetingData['discussion'] ?? null,
                'follow_up_required' => $meetingData['follow_up_required'] ?? false,
                'notes' => $meetingData['notes'] ?? null,
                'photo_path' => $meetingData['photo_path'] ?? null,
            ]);

            $participantIds = ! empty($meetingData['select_all_volunteers'])
                ? User::role('user')->where('is_active', true)->pluck('id')->all()
                : ($meetingData['participant_ids'] ?? []);

            $participantIds = array_values(array_diff(array_unique($participantIds), [$creator->id]));

            if (empty($participantIds)) {
                continue;
            }

            $meeting->participants()->attach($participantIds, ['notified_at' => $report->status === 'submitted' ? now() : null]);

            if ($report->status === 'submitted') {
                Notification::send(User::whereIn('id', $participantIds)->get(), new MeetingAssignedNotification($meeting));
            }
        }
    }

    private function syncTaskProgress(User $user, DailyReport $report, array $taskProgress): void
    {
        foreach ($taskProgress as $entry) {
            $target = Target::query()->applicableTo($user)->find($entry['target_id']);

            if (! $target) {
                continue;
            }

            $this->progressUpdater->recordManualProgress($user, $target, Carbon::parse($report->report_date), $entry);
        }
    }
}
