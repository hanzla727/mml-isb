<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Contact;
use App\Models\Department;
use App\Models\ExpenseClaim;
use App\Models\FormTemplate;
use App\Models\LeaveRequest;
use App\Models\Media;
use App\Models\Na;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Target;
use App\Models\Team;
use App\Models\Uc;
use App\Models\User;
use App\Models\VolunteerDocument;
use App\Services\DailyReportManager;
use App\Services\ReportApprovalService;
use App\Services\ScheduledMeetingService;
use App\Services\TargetProgressUpdater;
use App\Services\TaskWorkflowService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Populates every table in the app with realistic, interconnected demo data
 * so the whole NA/UC-based system can be browsed end-to-end without
 * manually creating records first. Assumes RolePermissionSeeder +
 * DepartmentTeamSeeder + DemoUserSeeder have already run (see
 * DatabaseSeeder) — it only looks up nas/ucs/users/teams/departments those
 * seeders create, it doesn't create its own hierarchy.
 */
class DemoDataSeeder extends Seeder
{
    private User $superAdmin;

    private User $admin1;

    private User $admin2;

    private User $naHead1;

    private User $teamLeader1;

    /** @var \Illuminate\Support\Collection<int, User> */
    private $volunteers;

    /** @var \Illuminate\Support\Collection<int, Contact> */
    private $contacts;

    /** @var \Illuminate\Support\Collection<int, Project> */
    private $projects;

    public function run(): void
    {
        $this->superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();
        $this->admin1 = User::where('email', 'admin1@example.com')->firstOrFail();
        $this->admin2 = User::where('email', 'admin2@example.com')->firstOrFail();
        $this->naHead1 = User::where('email', 'nahead1@example.com')->firstOrFail();
        $this->teamLeader1 = User::where('email', 'teamleader1@example.com')->firstOrFail();
        $this->volunteers = User::role('user')->orderBy('email')->get();

        $this->seedSettings();
        $this->contacts = $this->seedContacts();
        $this->seedTargets();
        $this->seedAnnouncements();
        $this->projects = $this->seedProjects();
        $formTemplate = $this->seedFormTemplate();
        [$pastMeetings, $upcomingMeetings] = $this->seedMeetings($formTemplate);
        $this->seedTasks($pastMeetings, $upcomingMeetings, $formTemplate);
        $this->seedMeetingAttendance($pastMeetings);
        $this->seedDailyReports();
        $this->seedLeaveRequests();
        $this->seedExpenseClaims();
        $this->seedVolunteerDocuments();
    }

    private function seedSettings(): void
    {
        Setting::set('organization_name', 'Herd Community Volunteers', 'general');
        Setting::set('organization_email', 'contact@herd-volunteers.example', 'general');
        Setting::set('daily_report_reminder_time', '18:00', 'notifications');
        Setting::set('missed_report_grace_hours', '3', 'notifications');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Contact>
     */
    private function seedContacts()
    {
        $people = [
            ['name' => 'Ayesha Khan', 'phone' => '0300-1111111', 'address' => 'F-10 Markaz, Islamabad'],
            ['name' => 'Bilal Ahmed', 'phone' => '0300-2222222', 'address' => 'F-11, Islamabad'],
            ['name' => 'Sana Malik', 'phone' => '0301-3333333', 'address' => 'G-9 Markaz, Islamabad'],
            ['name' => 'Usman Tariq', 'phone' => '0301-4444444', 'address' => 'F-8, Islamabad'],
            ['name' => 'Hina Riaz', 'phone' => '0302-5555555', 'address' => 'G-10, Islamabad'],
            ['name' => 'Fahad Siddiqui', 'phone' => '0302-6666666', 'address' => 'G-11, Islamabad'],
            ['name' => 'Mehwish Aslam', 'phone' => '0303-7777777', 'address' => 'F-7, Islamabad'],
            ['name' => 'Kamran Yousaf', 'phone' => '0303-8888888', 'address' => 'F-6, Islamabad'],
            ['name' => 'Nadia Sheikh', 'phone' => '0304-9999999', 'address' => 'G-8, Islamabad'],
            ['name' => 'Imran Qureshi', 'phone' => '0304-1010101', 'address' => 'I-8, Islamabad'],
        ];

        $creators = $this->volunteers->concat([$this->admin1, $this->admin2]);

        return collect($people)->map(fn (array $person, int $i) => Contact::create([
            ...$person,
            'notes' => 'Long-term community contact.',
            'created_by' => $creators[$i % $creators->count()]->id,
        ]));
    }

    private function seedTargets(): void
    {
        $donorRelationsTeam = Team::where('name', 'Donor Relations Team')->firstOrFail();
        $fundraising = Department::where('name', 'Fundraising')->firstOrFail();

        $targets = [
            [
                'title' => 'Monthly Field Hours', 'metric' => 'hours', 'type' => 'monthly',
                'scope' => 'all', 'scope_id' => null, 'target_value' => 40,
                'description' => 'Every volunteer should log at least 40 field hours a month.',
            ],
            [
                'title' => 'Weekly Donor Visits', 'metric' => 'meetings', 'type' => 'weekly',
                'scope' => 'team', 'scope_id' => $donorRelationsTeam->id, 'target_value' => 5,
                'description' => 'The Donor Relations Team should complete 5 visits per week.',
            ],
            [
                'title' => 'Quarterly Fundraising Drive', 'metric' => 'custom', 'type' => 'monthly',
                'scope' => 'department', 'scope_id' => $fundraising->id, 'target_value' => 500000,
                'description' => 'Fundraising department donation target, tracked manually (PKR).',
            ],
        ];

        foreach ($targets as $data) {
            Target::create([
                ...$data,
                'start_date' => Carbon::today()->startOfMonth(),
                'end_date' => Carbon::today()->endOfMonth(),
                'is_active' => true,
                'created_by' => $this->superAdmin->id,
            ]);
        }

        $updater = app(TargetProgressUpdater::class);
        $customTarget = Target::where('metric', 'custom')->firstOrFail();

        foreach ($this->volunteers as $volunteer) {
            $updater->syncForUser($volunteer, Carbon::today());
        }

        $updater->recordManualProgress($this->admin1, $customTarget, Carbon::today(), [
            'current_value' => 175000, 'notes' => 'On track for this quarter.', 'is_completed' => false,
        ]);
    }

    private function seedAnnouncements(): void
    {
        $na48 = Na::where('name', 'NA-48')->firstOrFail();
        $fundraising = Department::where('name', 'Fundraising')->firstOrFail();
        $hospital = Department::where('name', 'Hospital')->firstOrFail();

        $announcements = [
            ['title' => 'Welcome to the new volunteer portal', 'audience_scope' => 'all', 'audience_id' => null, 'category' => 'general'],
            ['title' => 'NA-48: new safety checklist', 'audience_scope' => 'na', 'audience_id' => $na48->id, 'category' => 'general'],
            ['title' => 'Fundraising Q3 kickoff meeting notes', 'audience_scope' => 'department', 'audience_id' => $fundraising->id, 'category' => 'event'],
            ['title' => 'Hospital ward visiting hours updated', 'audience_scope' => 'department', 'audience_id' => $hospital->id, 'category' => 'general'],
        ];

        foreach ($announcements as $data) {
            Announcement::create([
                ...$data,
                'body' => 'See the attached details and reach out to your team leader with questions.',
                'created_by' => $this->superAdmin->id,
                'published_at' => now()->subDays(random_int(1, 10)),
            ]);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Project>
     */
    private function seedProjects()
    {
        $fundraising = Department::where('name', 'Fundraising')->firstOrFail();
        $hospital = Department::where('name', 'Hospital')->firstOrFail();
        $ucF10 = Uc::where('name', 'UC F-10')->firstOrFail();
        $ucG9 = Uc::where('name', 'UC G-9')->firstOrFail();

        $projects = [
            ['department_id' => $fundraising->id, 'uc_id' => $ucF10->id, 'name' => 'Winter Relief Drive', 'status' => 'active', 'start_date' => now()->subMonth(), 'end_date' => now()->addMonth()],
            // Same Fundraising department as above, but running in UC G-9 —
            // demonstrates one department reused across UCs/NAs.
            ['department_id' => $fundraising->id, 'uc_id' => $ucG9->id, 'name' => 'Annual Fundraising Gala', 'status' => 'planning', 'start_date' => now()->addMonths(2), 'end_date' => now()->addMonths(2)->addDays(1)],
            ['department_id' => $hospital->id, 'uc_id' => $ucF10->id, 'name' => 'Spring Health Camp', 'status' => 'completed', 'start_date' => now()->subMonths(3), 'end_date' => now()->subMonths(2)],
        ];

        return collect($projects)->map(fn (array $data) => Project::create([
            ...$data,
            'description' => "{$data['name']} — department-run initiative.",
            'created_by' => $this->admin1->id,
        ]));
    }

    private function seedFormTemplate(): FormTemplate
    {
        $formTemplate = FormTemplate::create([
            'name' => 'Field Visit Checklist',
            'description' => 'Quick post-visit checklist attached to field tasks.',
            'created_by' => $this->admin1->id,
        ]);

        $formTemplate->fields()->createMany([
            ['label' => 'Location Visited', 'field_type' => 'text', 'is_required' => true, 'sort_order' => 0],
            ['label' => 'Visit Outcome', 'field_type' => 'dropdown', 'options' => ['choices' => ['Successful', 'Follow-up Needed', 'Unable to Contact']], 'is_required' => true, 'sort_order' => 1],
            ['label' => 'Additional Notes', 'field_type' => 'textarea', 'is_required' => false, 'sort_order' => 2],
        ]);

        return $formTemplate;
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, ScheduledMeeting>, 1: \Illuminate\Support\Collection<int, ScheduledMeeting>}
     */
    private function seedMeetings(FormTemplate $formTemplate): array
    {
        $service = app(ScheduledMeetingService::class);
        $winterRelief = $this->projects->firstWhere('name', 'Winter Relief Drive');

        $past = collect();
        foreach ([14, 7, 3] as $daysAgo) {
            $past->push($service->create($this->admin1, [
                'title' => "Donor Outreach Session (-{$daysAgo}d)",
                'meeting_date' => now()->subDays($daysAgo)->toDateString(),
                'start_time' => '10:00', 'end_time' => '12:00',
                'project_id' => $winterRelief->id,
                'organizer_id' => $this->teamLeader1->id,
                'scope' => 'team', 'team_id' => $this->teamLeader1->team_id,
            ]));
        }

        $upcoming = collect();

        $upcoming->push($service->create($this->admin1, [
            'title' => 'Volunteer Orientation',
            'meeting_date' => now()->addDays(2)->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:30',
            'form_template_id' => $formTemplate->id,
            'organizer_id' => $this->admin1->id,
            'scope' => 'all', 'user_ids' => [],
        ]));

        $upcoming->push($service->create($this->admin1, [
            'title' => 'Weekly Donor Relations Sync',
            'meeting_date' => now()->addDay()->toDateString(),
            'start_time' => '11:00', 'end_time' => '11:30',
            'organizer_id' => $this->teamLeader1->id,
            'scope' => 'team', 'team_id' => $this->teamLeader1->team_id,
            'is_recurring' => true, 'recurrence_frequency' => 'weekly', 'recurrence_interval' => 1,
            'recurrence_until' => now()->addWeeks(6)->toDateString(),
        ]));

        $upcoming->push($service->create($this->naHead1, [
            'title' => 'NA-48 Review',
            'meeting_date' => now()->addDays(5)->toDateString(),
            'start_time' => '15:00', 'end_time' => '16:00',
            'organizer_id' => $this->naHead1->id,
            'scope' => 'na', 'na_id' => $this->naHead1->na_id,
        ]));

        return [$past, $upcoming];
    }

    private function seedTasks($pastMeetings, $upcomingMeetings, FormTemplate $formTemplate): void
    {
        $service = app(TaskWorkflowService::class);
        $winterRelief = $this->projects->firstWhere('name', 'Winter Relief Drive');
        $donorRelationsTeamId = $this->teamLeader1->team_id;
        $teamVolunteers = $this->volunteers->where('team_id', $donorRelationsTeamId)->values();

        // A completed, reviewed task with an attached dynamic form + submission.
        $checklistTask = $service->create($this->admin1, [
            'scheduled_meeting_id' => $pastMeetings->first()->id,
            'project_id' => $winterRelief->id,
            'form_template_id' => $formTemplate->id,
            'title' => 'Visit Ayesha Khan household',
            'description' => 'Follow up on relief package delivery.',
            'priority' => 'high',
            'due_date' => now()->subDays(10)->toDateString(),
            'scope' => 'individual', 'user_ids' => [$teamVolunteers->first()->id],
        ]);

        $report = $service->submitReport($checklistTask, $teamVolunteers->first(), [
            'work_summary' => 'Delivered winter relief package and checked on the family.',
            'working_hours' => 3,
            'amount_collected' => 5000,
        ]);
        $service->review($report, $this->teamLeader1, 'approve');

        $submission = $checklistTask->formSubmissions()->create([
            'form_template_id' => $formTemplate->id,
            'user_id' => $teamVolunteers->first()->id,
        ]);
        $fields = $formTemplate->fields;
        $submission->values()->create(['form_field_id' => $fields[0]->id, 'value' => 'F-10 Markaz, Islamabad']);
        $submission->values()->create(['form_field_id' => $fields[1]->id, 'value' => 'Successful']);
        $submission->values()->create(['form_field_id' => $fields[2]->id, 'value' => 'Family is doing well, no further action needed.']);

        // A rejected-then-resubmitted task.
        $revisedTask = $service->create($this->admin1, [
            'project_id' => $winterRelief->id,
            'title' => 'Coordinate relief supply drop-off',
            'priority' => 'medium',
            'due_date' => now()->subDays(5)->toDateString(),
            'scope' => 'individual', 'user_ids' => [$teamVolunteers->get(1)?->id ?? $teamVolunteers->first()->id],
        ]);
        $assignee = $revisedTask->assignees->first();
        $firstReport = $service->submitReport($revisedTask, $assignee, ['work_summary' => 'Dropped off supplies at the wrong location.']);
        $service->review($firstReport, $this->admin1, 'return_for_revision', 'Wrong address — please confirm and resubmit.');
        $secondReport = $service->submitReport($revisedTask, $assignee, ['work_summary' => 'Corrected and delivered to the right address.']);
        $service->review($secondReport, $this->admin1, 'approve_with_remarks', 'Good recovery, thanks for the quick fix.');

        $service->addComment($revisedTask, $this->admin1, 'Great teamwork sorting this out quickly.');
        $service->addComment($revisedTask, $assignee, 'Thanks, will double check addresses going forward.');

        // Tasks awaiting review / in progress, for the review queues to have content.
        $service->create($this->admin1, [
            'scheduled_meeting_id' => $pastMeetings->get(1)->id,
            'title' => 'Log outreach session outcomes',
            'priority' => 'medium',
            'due_date' => now()->addDays(2)->toDateString(),
            'scope' => 'team', 'team_id' => $donorRelationsTeamId,
        ]);

        $service->create($this->admin2, [
            'title' => 'Draft donor thank-you letters',
            'priority' => 'low',
            'due_date' => now()->subDays(2)->toDateString(),
            'scope' => 'individual', 'user_ids' => [$this->volunteers->firstWhere('email', 'volunteer5@example.com')->id],
        ]);

        $service->create($this->admin1, [
            'scheduled_meeting_id' => $upcomingMeetings->first()->id,
            'title' => 'Prepare orientation materials',
            'priority' => 'medium',
            'due_date' => now()->addDay()->toDateString(),
            'scope' => 'individual', 'user_ids' => [$teamVolunteers->first()->id],
            'is_recurring' => true, 'recurrence_frequency' => 'monthly', 'recurrence_interval' => 1,
        ]);

        // NA-wide task, assigned via the "na" audience scope.
        $service->create($this->naHead1, [
            'title' => 'Submit monthly NA summary',
            'priority' => 'medium',
            'due_date' => now()->addDays(7)->toDateString(),
            'scope' => 'na', 'na_id' => $this->naHead1->na_id,
        ]);

        // A pending report awaiting review (leave one report un-reviewed).
        $pendingTask = $service->create($this->admin1, [
            'title' => 'Update donor contact list',
            'priority' => 'medium',
            'due_date' => now()->subDay()->toDateString(),
            'scope' => 'individual', 'user_ids' => [$this->volunteers->firstWhere('email', 'volunteer2@example.com')->id],
        ]);
        $service->submitReport($pendingTask, $pendingTask->assignees->first(), ['work_summary' => 'Updated 15 donor records.']);
    }

    private function seedMeetingAttendance($pastMeetings): void
    {
        $statuses = ['present', 'present', 'late', 'absent', 'excused'];

        foreach ($pastMeetings as $meeting) {
            foreach ($meeting->participants as $i => $participant) {
                $meeting->attendances()->create([
                    'user_id' => $participant->id,
                    'status' => $statuses[$i % count($statuses)],
                    'marked_by' => $this->teamLeader1->id,
                    'marked_at' => Carbon::parse($meeting->meeting_date)->addHours(13),
                ]);
            }
        }
    }

    private function seedDailyReports(): void
    {
        $manager = app(DailyReportManager::class);
        $approval = app(ReportApprovalService::class);
        $categories = ['family_visit', 'follow_up', 'fund_discussion', 'general'];

        foreach ($this->volunteers as $volunteer) {
            for ($daysAgo = 4; $daysAgo >= 1; $daysAgo--) {
                $reportDate = now()->subDays($daysAgo);
                $status = $daysAgo === 4 ? 'draft' : 'submitted';

                $report = $manager->create($volunteer, [
                    'report_date' => $reportDate->toDateString(),
                    'field_start_time' => '09:00',
                    'field_end_time' => '15:00',
                    'summary' => 'Conducted field visits and follow-ups as planned.',
                    'challenges' => $daysAgo === 2 ? 'Traffic delays affected the schedule.' : null,
                    'tomorrow_plan' => 'Continue with the scheduled visits.',
                    'status' => $status,
                    'meetings' => [
                        [
                            'contact_id' => $this->contacts->random()->id,
                            'category' => $categories[$daysAgo % count($categories)],
                            'discussion' => 'Discussed ongoing support and next steps.',
                        ],
                    ],
                ]);

                if ($status !== 'submitted') {
                    continue;
                }

                if ($report->review_status === 'pending_review' && $daysAgo !== 1) {
                    // Leave the most recent one pending; drive the rest through review.
                    $decision = $daysAgo === 2 ? 'needs_revision' : 'recommend_approve';
                    $report = $approval->teamLeaderReview($report, $this->teamLeader1, $decision, 'Reviewed by team leader.');

                    if ($decision === 'recommend_approve') {
                        $approval->adminReview($report, $this->admin1, $daysAgo === 3 ? 'approve_with_remarks' : 'approve', 'Looks good.');
                    }
                } elseif ($report->review_status === 'under_review' && $daysAgo !== 1) {
                    $decision = $daysAgo === 2 ? 'reject' : 'approve';
                    $approval->adminReview($report, $this->reviewerFor($volunteer), $decision, $decision === 'reject' ? 'Please provide more detail.' : 'Approved.');
                }
            }
        }
    }

    /**
     * The Admin who can actually see/act on this user via HierarchyScope
     * (matched by their assigned NAs), falling back to Super Admin for
     * anyone outside every seeded Admin's NA assignments.
     */
    private function reviewerFor(User $user): User
    {
        if ($user->na_id === null) {
            return $this->superAdmin;
        }

        foreach ([$this->admin1, $this->admin2] as $admin) {
            if ($admin->adminNas()->where('nas.id', $user->na_id)->exists()) {
                return $admin;
            }
        }

        return $this->superAdmin;
    }

    private function seedLeaveRequests(): void
    {
        $volunteers = $this->volunteers->values();

        $requests = [
            ['user' => $volunteers[0], 'type' => 'sick', 'start' => now()->subDay(), 'end' => now()->addDay(), 'status' => 'approved'],
            ['user' => $volunteers[1], 'type' => 'vacation', 'start' => now()->addWeek(), 'end' => now()->addWeek()->addDays(3), 'status' => 'approved'],
            ['user' => $volunteers[2], 'type' => 'personal', 'start' => now()->addDays(3), 'end' => now()->addDays(3), 'status' => 'pending'],
            ['user' => $volunteers[3], 'type' => 'emergency', 'start' => now()->subDays(2), 'end' => now()->subDays(2), 'status' => 'rejected'],
            ['user' => $volunteers[4], 'type' => 'other', 'start' => now()->addDays(10), 'end' => now()->addDays(11), 'status' => 'pending'],
        ];

        foreach ($requests as $data) {
            $leaveRequest = LeaveRequest::create([
                'user_id' => $data['user']->id,
                'leave_type' => $data['type'],
                'start_date' => $data['start']->toDateString(),
                'end_date' => $data['end']->toDateString(),
                'reason' => ucfirst($data['type']).' leave request.',
            ]);

            if ($data['status'] !== 'pending') {
                $reviewer = $this->reviewerFor($data['user']);
                $data['status'] === 'approved' ? $leaveRequest->approve($reviewer) : $leaveRequest->reject($reviewer);
            }
        }
    }

    private function seedExpenseClaims(): void
    {
        $volunteers = $this->volunteers->values();

        $claims = [
            ['user' => $volunteers[0], 'type' => 'travel', 'amount' => 45.50, 'status' => 'approved'],
            ['user' => $volunteers[1], 'type' => 'supplies', 'amount' => 120.00, 'status' => 'pending'],
            ['user' => $volunteers[2], 'type' => 'food', 'amount' => 25.00, 'status' => 'approved'],
            ['user' => $volunteers[5], 'type' => 'accommodation', 'amount' => 80.00, 'status' => 'rejected'],
            ['user' => $volunteers[6], 'type' => 'other', 'amount' => 15.00, 'status' => 'pending'],
        ];

        foreach ($claims as $data) {
            $expenseClaim = ExpenseClaim::create([
                'user_id' => $data['user']->id,
                'expense_type' => $data['type'],
                'amount' => $data['amount'],
                'date' => now()->subDays(random_int(1, 14))->toDateString(),
                'description' => ucfirst($data['type']).' expense during field work.',
            ]);

            if ($data['status'] !== 'pending') {
                $reviewer = $this->reviewerFor($data['user']);
                $data['status'] === 'approved' ? $expenseClaim->approve($reviewer) : $expenseClaim->reject($reviewer);
            }
        }
    }

    private function seedVolunteerDocuments(): void
    {
        $volunteers = $this->volunteers->values();

        $documents = [
            ['user' => $volunteers[0], 'title' => 'CNIC Copy', 'type' => 'cnic'],
            ['user' => $volunteers[0], 'title' => 'Volunteer Agreement', 'type' => 'agreement'],
            ['user' => $volunteers[1], 'title' => 'First Aid Training Certificate', 'type' => 'training'],
        ];

        foreach ($documents as $data) {
            $document = VolunteerDocument::create([
                'user_id' => $data['user']->id,
                'title' => $data['title'],
                'document_type' => $data['type'],
                'uploaded_by' => $data['user']->id,
            ]);

            $path = "volunteer-documents/demo-{$document->id}.txt";
            Storage::disk('public')->put($path, "Demo placeholder for: {$data['title']}");

            $document->file()->save(new Media([
                'disk' => 'public',
                'path' => $path,
                'mime_type' => 'text/plain',
                'size' => Storage::disk('public')->size($path),
            ]));
        }
    }
}
