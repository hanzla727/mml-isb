<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('How This System Works') }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        .guide-body {
            max-width: 1020px;
            margin: 0 auto;
            padding: 2rem 1rem 5rem;
        }

        .guide-toc {
            position: sticky;
            top: 1rem;
        }

        .guide-toc a {
            display: block;
            padding: .3rem .6rem;
            border-radius: .4rem;
            color: #495057;
            text-decoration: none;
            font-size: .87rem;
        }

        .guide-toc a:hover {
            background: #eef0fb;
            color: #4f46e5;
        }

        .flow-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
        }

        .flow-box {
            background: #fff;
            border: 1px solid #e2e4f0;
            border-radius: .6rem;
            padding: .85rem 1.1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
            flex: 1 1 160px;
            text-align: center;
        }

        .flow-box strong {
            display: block;
            color: #4f46e5;
        }

        .flow-box small {
            color: #6c757d;
        }

        .flow-arrow {
            font-size: 1.4rem;
            color: #9aa0c3;
            flex: 0 0 auto;
        }

        .urdu {
            color: #6c757d;
            font-size: .88rem;
        }

        .role-card {
            border: 1px solid #e2e4f0;
            border-radius: .6rem;
            padding: 1rem 1.25rem;
            height: 100%;
            background: #fff;
        }

        .role-card h5 {
            color: #4f46e5;
        }

        section h2 {
            padding-top: 1rem;
            border-top: 1px solid #eee;
            margin-top: 3rem;
        }

        section:first-of-type h2 {
            border-top: none;
            margin-top: 0;
        }

        .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 50%;
            background: #4f46e5;
            color: #fff;
            font-weight: 600;
            font-size: .9rem;
            flex: 0 0 auto;
        }

        .mockup {
            background: #f8f9fc;
            border: 1px dashed #c7cae8;
            border-radius: .6rem;
            padding: .9rem 1.1rem;
            font-size: .87rem;
        }

        .mockup .mockup-title {
            font-weight: 600;
            color: #4f46e5;
            margin-bottom: .35rem;
        }

        .quickstart-card {
            border: 1px solid #e2e4f0;
            border-radius: .7rem;
            padding: 1.1rem;
            background: linear-gradient(180deg, #fff, #f8f9fc);
            height: 100%;
        }

        .quickstart-card h5 {
            color: #4f46e5;
        }

        .story-msg {
            border-left: 3px solid #4f46e5;
            padding: .6rem 1rem;
            background: #fff;
            border-radius: 0 .5rem .5rem 0;
            margin-bottom: .75rem;
        }

        .story-msg .who {
            font-weight: 700;
            color: #4f46e5;
        }

        .glossary-term {
            font-weight: 600;
        }

        table.status-table td,
        table.status-table th {
            vertical-align: middle;
        }

        .menu-preview {
            background: #1e1b3a;
            color: #cfd0e0;
            border-radius: .6rem;
            padding: .9rem 1rem;
            font-size: .85rem;
        }

        .menu-preview .mp-item {
            padding: .25rem .4rem;
            border-radius: .35rem;
        }

        .menu-preview .mp-item.mp-active {
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark" style="background:#1e1b3a;">
        <div class="container-fluid">
            <span class="navbar-brand"><i class="bi bi-heart-fill"></i> {{ __('Volunteer Mgmt') }} &mdash; {{ __('User Guide') }}</span>
            <a href="{{ url('/') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> {{ __('Back to Dashboard') }}</a>
        </div>
    </nav>

    <div class="guide-body">

        <div class="text-center py-5">
            <h1 class="fw-bold">{{ __('How This System Works') }}</h1>
            <p class="text-muted fs-5">{{ __('A complete, easy-to-follow guide — from the big picture down to exactly what to click.') }}</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="quickstart-card">
                    <h5><i class="bi bi-person"></i> {{ __('New Volunteer? Start Here') }}</h5>
                    <ol class="small mb-0 ps-3">
                        <li>{{ __('Log in with the email/password you were given.') }}</li>
                        <li>{!! __('Open :myTasks and :meetings — that\'s everything assigned to you.', ['myTasks' => '<strong>' . __('My Tasks') . '</strong>', 'meetings' => '<strong>' . __('Meetings') . '</strong>']) !!}</li>
                        <li>{!! __('When you finish something, open it and :submit.', ['submit' => '<strong>' . __('submit a report') . '</strong>']) !!}</li>
                    </ol>
                </div>
            </div>
            <div class="col-md-6">
                <div class="quickstart-card">
                    <h5><i class="bi bi-person-workspace"></i> {{ __('New Admin / Team Leader? Start Here') }}</h5>
                    <ol class="small mb-0 ps-3">
                        <li>{!! __('Log in — you land on the :dashboard.', ['dashboard' => '<strong>' . __('Dashboard') . '</strong>']) !!}</li>
                        <li>{!! __('Create a :meeting or :task and pick who it\'s for.', ['meeting' => '<strong>' . __('Meeting') . '</strong>', 'task' => '<strong>' . __('Task') . '</strong>']) !!}</li>
                        <li>{!! __('Watch :reportReviews for work that needs your decision.', ['reportReviews' => '<strong>' . __('Report Reviews') . '</strong>']) !!}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="guide-toc">
                    <div class="text-uppercase text-muted small fw-semibold mb-2 px-2">{{ __('On this page') }}</div>
                    <a href="#structure">1. {{ __('Organization Structure') }}</a>
                    <a href="#roles">2. {{ __('Roles & Your Menu') }}</a>
                    <a href="#glossary">3. {{ __('Glossary') }}</a>
                    <a href="#story">4. {{ __('A Real Example, Start to Finish') }}</a>
                    <a href="#statuses">5. {{ __('What Every Status Means') }}</a>
                    <a href="#volunteer">6. {{ __('If You Are a Volunteer') }}</a>
                    <a href="#leader">7. {{ __('If You Manage Others') }}</a>
                    <a href="#modules">8. {{ __('Other Sections') }}</a>
                    <a href="#faq">9. {{ __('Frequently Asked Questions') }}</a>
                </div>
            </div>

            <div class="col-lg-9">

                <section id="structure">
                    <h2><i class="bi bi-diagram-3"></i> 1. {{ __('Organization Structure') }}</h2>
                    <p>{!! __('This organization works within :islamabadOnly. Islamabad Capital Territory has no Provincial Assembly — it\'s a federal territory, not part of any province — so the real structure here is just two levels: :naUc. Everyone in the system sits somewhere in this chain, and every volunteer has all levels set (NA is automatic, UC/Department/Team are explicit) — none of them are optional.', ['islamabadOnly' => '<strong>' . __('Islamabad only') . '</strong>', 'naUc' => '<strong>NA &rarr; UC</strong>']) !!}</p>
                    <p class="urdu">Yeh organization sirf Islamabad ke andar kaam karti hai. Islamabad me Provincial
                        Assembly nahi hoti, isliye asal structure sirf do level ka hai: NA &rarr; UC. Har volunteer ka
                        UC, Department, Team aur Reporting Head zaroor set hota hai.</p>

                    <div class="flow-row my-4">
                        <div class="flow-box"><strong><i class="bi bi-building"></i> {{ __('Organization') }}</strong><small>{{ __('The whole organization') }}</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-map"></i> NA</strong><small>{{ __('e.g. NA-48 — who\'s assigned') }}</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-pin-map"></i> UC</strong><small>{{ __('e.g. UC F-10 — where work happens') }}</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-people-fill"></i> {{ __('Team') }}</strong><small>{{ __('e.g. Donor Relations Team') }}</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-person"></i> {{ __('Volunteer') }}</strong><small>{{ __('Does the actual field work') }}</small></div>
                    </div>

                    <div class="mockup mb-3">
                        <div class="mockup-title"><i class="bi bi-signpost-split"></i> {{ __('What each level actually means') }}
                        </div>
                        <ul class="small mb-0 ps-3">
                            <li>{!! __('is the unit a person is actually put in charge of (its :naHead). Whatever UCs sit under that NA, all of them are that person\'s responsibility — that\'s the whole point of the NA level.', ['naHead' => '<strong>' . __('NA Head') . '</strong>']) !!}</li>
                            <li>{!! __(':ucLabel is where the real, on-the-ground work lives: Teams, Volunteers, and Projects all attach to a UC. :sectorLabel (e.g. "F-10") is just an optional, informal label you can put on a UC — it doesn\'t add another level, it\'s not required, and nothing in the system depends on it.', ['ucLabel' => '<strong>UC</strong>', 'sectorLabel' => '<strong>' . __('Sector') . '</strong>']) !!}</li>
                        </ul>
                    </div>

                    <div class="mockup mb-3">
                        <div class="mockup-title"><i class="bi bi-diagram-3"></i> {{ __('Department is shared across every NA/UC') }}</div>
                        <p class="mb-2 small">{!! __('Department (Fundraising, Hospital, Mosque, Khidmat, Dawah, Administration, ...) is :not part of the NA → UC chain above — it\'s the same org-wide list everywhere. A Team is what actually connects a Department to one specific UC. So the same "Fundraising" department can have a team in UC F-10 :and a separate team in UC G-9 — it\'s one shared category, used in more than one place.', ['not' => '<strong>' . __('not') . '</strong>', 'and' => '<em>' . __('and') . '</em>']) !!}</p>
                        <div class="mockup-title"><i class="bi bi-signpost-split"></i> {{ __('A real example from this system') }}
                        </div>
                        {!! __(':na48 (run by :naHeadOne) has two UCs: :ucF10 and :ucF11. :ucF10Again has :donorTeam (Team, under the :fundraising department, led by :teamLeaderOne) → :volunteerOne (a volunteer on that team). Meanwhile :na49 has its own :ucG9, with a :communityTeam — also under :fundraisingAgain.', [
                            'na48' => '<strong>NA-48</strong>',
                            'naHeadOne' => '<em>' . __('NA Head One') . '</em>',
                            'ucF10' => '<strong>UC F-10</strong>',
                            'ucF11' => '<strong>UC F-11</strong>',
                            'ucF10Again' => '<strong>UC F-10</strong>',
                            'donorTeam' => '<strong>' . __('Donor Relations Team') . '</strong>',
                            'fundraising' => '<em>' . __('Fundraising') . '</em>',
                            'teamLeaderOne' => '<em>' . __('Team Leader One') . '</em>',
                            'volunteerOne' => '<strong>' . __('Volunteer One') . '</strong>',
                            'na49' => '<strong>NA-49</strong>',
                            'ucG9' => '<strong>UC G-9</strong>',
                            'communityTeam' => '<strong>' . __('Community Fundraising Team') . '</strong>',
                            'fundraisingAgain' => '<em>' . __('Fundraising') . '</em>',
                        ]) !!}
                    </div>

                    <p class="mb-1">{!! __(':reportingHead is separate from the team/department chart. It\'s the one specific person answerable for a volunteer — usually their Team Leader, but it can be set to anyone (an NA Head, for a volunteer with no team leader yet). This is who gets notified first when that volunteer\'s work needs a decision.', ['reportingHead' => '<strong>' . __('Reporting Head') . '</strong>']) !!}</p>
                    <p class="mb-0">{!! __(':multipleNas an Admin can be responsible for more than one NA at once (e.g. both NA-48 and NA-49). An NA Head is always scoped to exactly one NA — but every UC in it.', ['multipleNas' => '<strong>' . __('Multiple NAs:') . '</strong>']) !!}</p>
                </section>

                <section id="roles">
                    <h2><i class="bi bi-person-badge"></i> 2. {{ __('Roles & Your Menu') }}</h2>
                    <p>{{ __("There are 5 roles. Each one only sees what's relevant to them — the menu on the left (or top, if you're a volunteer) changes automatically based on your role.") }}</p>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-shield-lock"></i> {{ __('Super Admin') }}</h5>
                                <p class="small mb-2">{{ __('Sees and manages every NA/UC, every user, every setting. Usually just one or two people at the top of the organization.') }}</p>
                                <p class="small text-muted mb-0"><strong>{{ __('Example:') }}</strong> {{ __('Super Admin') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-shield"></i> {{ __('Admin') }}</h5>
                                <p class="small mb-2">{{ __('Manages one or more assigned NAs: creates meetings/tasks, reviews reports, manages users, departments and teams — but only within their own NA(s).') }}</p>
                                <p class="small text-muted mb-0"><strong>{{ __('Example:') }}</strong> {{ __('Admin One → manages NA-48') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-geo-alt"></i> {{ __('NA Head') }}</h5>
                                <p class="small mb-2">{{ __('Same abilities as Admin, but always scoped to exactly one NA — and every UC underneath it — the one they were appointed to lead.') }}</p>
                                <p class="small text-muted mb-0"><strong>{{ __('Example:') }}</strong> {{ __('NA Head One → leads NA-48 (both UC F-10 and UC F-11)') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-people-fill"></i> {{ __('Team Leader') }}</h5>
                                <p class="small mb-2">{{ __("Runs one Team. Reviews that team's daily reports first, sees the team's tasks/meetings, and also has their own personal tasks like a volunteer does.") }}
                                </p>
                                <p class="small text-muted mb-0"><strong>{{ __('Example:') }}</strong> {{ __('Team Leader One → leads Donor Relations Team') }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="role-card">
                                <h5><i class="bi bi-person"></i> {{ __('Volunteer') }}</h5>
                                <p class="small mb-2">{{ __('Sees only their own meetings, tasks, reports, targets, and leave/expense history. This is the majority of the organization.') }}</p>
                                <p class="small text-muted mb-0"><strong>{{ __('Example:') }}</strong> {{ __('Volunteer One → on Donor Relations Team, reports to Team Leader One') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="menu-preview">
                                <div class="fw-semibold mb-2 text-white"><i class="bi bi-list"></i> {{ __('Sidebar — Admin / NA Head / Team Leader') }}</div>
                                <div class="mp-item mp-active">{{ __('Dashboard') }}</div>
                                <div class="mp-item">{{ __('Search') }}</div>
                                <div class="mp-item">{{ __('NAs') }} <span class="text-muted">{{ __('(+ UCs, super admin & NA managers)') }}</span></div>
                                <div class="mp-item">{{ __('Reports') }} &middot; {{ __('Meetings') }} &middot; {{ __('Tasks') }} &middot; {{ __('Report Reviews') }}
                                    &middot; {{ __('Projects') }}</div>
                                <div class="mp-item">{{ __('Users') }} &middot; {{ __('Departments') }} &middot; {{ __('Teams') }} &middot; {{ __('My Team') }}</div>
                                <div class="mp-item">{{ __('Leave Requests') }} &middot; {{ __('Expense Claims') }}</div>
                                <div class="mp-item">{{ __('Announcements') }} &middot; {{ __('Targets') }} &middot; {{ __('Analytics') }} &middot;
                                    {{ __('Performance') }} &middot; {{ __('Forms') }}</div>
                                <div class="mp-item">{{ __('How It Works') }}</div>
                            </div>
                            <p class="small text-muted mt-1 mb-0">{{ __('Some items only appear if your role has that specific permission — e.g. a Team Leader won\'t see "Users" or "NAs".') }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="menu-preview">
                                <div class="fw-semibold mb-2 text-white"><i class="bi bi-list"></i> {{ __('Top Menu — Volunteer') }}</div>
                                <div class="mp-item mp-active">{{ __('Dashboard') }}</div>
                                <div class="mp-item">{{ __('My Reports') }} &middot; {{ __('Field Visits') }} &middot; {{ __('Meetings') }}</div>
                                <div class="mp-item">{{ __('My Tasks') }} &middot; {{ __('My Targets') }}</div>
                                <div class="mp-item">{{ __('Announcements') }} &middot; {{ __('My Progress') }} &middot; {{ __('My Performance') }}</div>
                                <div class="mp-item">{{ __('My Leave') }} &middot; {{ __('My Expenses') }} &middot; {{ __('My Documents') }}</div>
                                <div class="mp-item">{{ __('How It Works') }}</div>
                            </div>
                            <p class="small text-muted mt-1 mb-0">{{ __('Everything here is scoped to "my" — a volunteer never sees anyone else\'s data.') }}</p>
                        </div>
                    </div>
                </section>

                <section id="glossary">
                    <h2><i class="bi bi-book"></i> 3. {{ __('Glossary — Every Term, Explained') }}</h2>
                    <p>{{ __('Click any term to expand it. Come back here any time something is unclear.') }}</p>

                    <div class="accordion" id="glossaryAccordion">
                        @php
                            $terms = [
                                ['NA', __('The unit a person is actually assigned to manage (e.g. "NA-48"), via an "NA Head". Every UC under that NA is that person\'s responsibility. It is the real top-level structure here — Islamabad has no Provincial Assembly, so there is no level above NA.')],
                                ['UC', __('The bottom-most operational unit (e.g. "UC F-10") — this is where Teams, Projects, and Volunteers actually attach. One NA can have several UCs.')],
                                [__('Sector'), __('A purely optional, informal label you can put on a UC (e.g. "F-10"). It is not a structural level of its own and nothing depends on it.')],
                                [__('Department'), __('A shared, org-wide function, e.g. "Fundraising" or "Hospital" — the same list applies to every UC/NA, it is never specific to just one. To give a department a presence in a specific UC, you create a Team for it there.')],
                                [__('Team'), __('The UC-specific instance of a Department — e.g. "Donor Relations Team" is Fundraising\'s team in UC F-10. Led by one Team Leader. The same Department can have different Teams in different UCs.')],
                                [__('Reporting Head'), __('The one specific person responsible for a volunteer. Usually the Team Leader, but can be reassigned to anyone (e.g. an NA Head).')],
                                [__('Project'), __('A larger initiative that groups several meetings and tasks together under one name, with its own progress percentage. Belongs to one Department and one UC at the same time (e.g. a Fundraising project running specifically in UC F-10).')],
                                [__('Meeting'), __('A scheduled event (date, time, location, agenda) with invited participants. Tasks can optionally be linked to a meeting, but don\'t have to be.')],
                                [__('Task'), __('A specific piece of work assigned to one or more people, with a priority and due date. Can exist completely on its own, with no meeting attached.')],
                                [__('Scope / Audience'), __('Who a meeting or task is for. One of: a specific person or people, an entire Team, an entire Department, an entire UC, an entire NA, several NAs at once, or every volunteer in the organization.')],
                                [__('Report'), __('What a volunteer submits after doing the work — a written summary, working hours, amount collected (if any), and file attachments like receipts.')],
                                [__('Version'), __('Every time a report is sent back and resubmitted, it becomes a new version (v1, v2, v3...). Nothing is ever deleted or overwritten.')],
                                [__('Review'), __('The decision a reviewer makes on a submitted report: Approve, Approve with Remarks, Reject, Return for Revision, or Request More Information.')],
                                [__('Attachment'), __('Any file uploaded with a report — a receipt, a photo, a document.')],
                                [__('Target'), __('A goal set for a person, team, UC, or NA with a deadline, tracked until it\'s met.')],
                                [__('Announcement'), __('A one-way message from leadership to a Team, Department, UC, NA, or everyone.')],
                                [__('NA Dashboard'), __('A single screen showing an NA\'s health: volunteers, meetings, attendance, tasks, reports, fund collection, and working hours.')],
                                [__('NA Comparison / Ranking'), __('A side-by-side comparison of NAs against each other, for management planning only — not a leaderboard, and never about individual volunteers.')],
                            ];
                        @endphp
                        @foreach ($terms as $i => $term)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#term{{ $i }}">
                                        <span class="glossary-term">{{ $term[0] }}</span>
                                    </button>
                                </h2>
                                <div id="term{{ $i }}" class="accordion-collapse collapse"
                                    data-bs-parent="#glossaryAccordion">
                                    <div class="accordion-body small">{!! $term[1] !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="story">
                    <h2><i class="bi bi-arrow-repeat"></i> 4. {{ __('A Real Example, Start to Finish') }}</h2>
                    <p>{{ __('Here is the exact same journey a real task takes in this system, told as a short story. This is the one flow everything else supports.') }}</p>
                    <p class="urdu">Yeh ek chota sa real example hai jo dikhata hai ke ek task shuru se end tak kaise
                        chalta hai.</p>

                    <div class="mt-4">
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-1-circle-fill"></i> {{ __('Admin One (Admin, NA-48) creates a task') }}
                            </div>
                            {!! __('Opens :tasksAddTask. Title: "Collect donations after Friday prayer". Priority: Medium. Under "Assign To" picks scope :entireTeam and selects :donorTeam. No meeting is attached — this task stands on its own. Saves it.', [
                                'tasksAddTask' => '<strong>' . __('Tasks → Add Task') . '</strong>',
                                'entireTeam' => '<strong>' . __('Entire Team') . '</strong>',
                                'donorTeam' => '<strong>' . __('Donor Relations Team') . '</strong>',
                            ]) !!}
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-2-circle-fill"></i> {{ __('The system assigns it instantly') }}</div>
                            {!! __('Every current member of Donor Relations Team — including :volunteerOne — is attached to the task right away and gets a notification. The task\'s status becomes :assigned.', [
                                'volunteerOne' => '<strong>' . __('Volunteer One') . '</strong>',
                                'assigned' => '<span class="badge bg-info text-dark">' . __('Assigned') . '</span>',
                            ]) !!}
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-3-circle-fill"></i> {{ __('Volunteer One logs in') }}</div>
                            {!! __('Opens :myTasks, sees "Collect donations after Friday prayer" waiting, and goes to do the actual collection at the mosque.', ['myTasks' => '<strong>' . __('My Tasks') . '</strong>']) !!}
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-4-circle-fill"></i> {{ __('Volunteer One submits a report') }}</div>
                            {!! __('Opens the task, fills in :workSummary ("Collected donations after Friday prayer"), :workingHours (2), :amountCollected (Rs. 5,000), and uploads a photo of the receipt as an attachment. Clicks :submitReport. Task status becomes :reportSubmitted.', [
                                'workSummary' => '<em>' . __('Work Summary') . '</em>',
                                'workingHours' => '<em>' . __('Working Hours') . '</em>',
                                'amountCollected' => '<em>' . __('Amount Collected') . '</em>',
                                'submitReport' => '<strong>' . __('Submit Report') . '</strong>',
                                'reportSubmitted' => '<span class="badge bg-info text-dark">' . __('Report Submitted') . '</span>',
                            ]) !!}
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-5-circle-fill"></i> {{ __('Team Leader One reviews it first') }}</div>
                            {!! __('Because Team Leader One leads Donor Relations Team, the report lands in their :reportReviews queue. They open it, read the summary, see the receipt, and check the amount matches. They approve it.', ['reportReviews' => '<strong>' . __('Report Reviews') . '</strong>']) !!}
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-6-circle-fill"></i> {{ __("It's done") }}</div>
                            {!! __('The report becomes :approved1 and the task becomes :approved2. Volunteer One gets notified. The Rs. 5,000 is now counted in :na48Dashboard under fund collection.', [
                                'approved1' => '<span class="badge bg-success">' . __('Approved') . '</span>',
                                'approved2' => '<span class="badge bg-success">' . __('Approved') . '</span>',
                                'na48Dashboard' => '<strong>' . __("NA-48's") . '</strong> ' . __('Dashboard'),
                            ]) !!}
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-exclamation-circle-fill"></i> {{ __('If instead it needed a fix…') }}</div>
                            {!! __('Team Leader One could have chosen :returnForRevision with a remark like "please attach a clearer receipt photo". The task would show :needsRevision, Volunteer One would see the remark, fix it, and resubmit — creating version 2 of the report. Nothing from version 1 is lost.', [
                                'returnForRevision' => '<strong>' . __('Return for Revision') . '</strong>',
                                'needsRevision' => '<span class="badge bg-warning text-dark">' . __('Needs Revision') . '</span>',
                            ]) !!}
                        </div>
                    </div>
                </section>

                <section id="statuses">
                    <h2><i class="bi bi-flag"></i> 5. {{ __('What Every Status Means') }}</h2>
                    <p>{{ __("Statuses can look intimidating at first glance — here's every one of them in plain language.") }}</p>

                    <h6 class="mt-3">{{ __('Task Status') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm status-table">
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-secondary">{{ __('Not Assigned') }}</span></td>
                                    <td class="small">{{ __('Created but nobody is attached to it yet.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">{{ __('Assigned') }}</span></td>
                                    <td class="small">{{ __('Someone (or a whole team/department/UC/NA) has been given this task.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">{{ __('In Progress') }}</span></td>
                                    <td class="small">{{ __('Work has started.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">{{ __('Waiting for Information') }}</span></td>
                                    <td class="small">{{ __('The reviewer asked a question before deciding — check the remarks.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">{{ __('Report Submitted') }}</span></td>
                                    <td class="small">{{ __("The volunteer has submitted their report; it's now waiting on a reviewer.") }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">{{ __('Under Review') }}</span></td>
                                    <td class="small">{{ __('A reviewer is actively looking at it.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">{{ __('Approved') }}</span></td>
                                    <td class="small">{{ __('Done and accepted — counted in dashboards and NA performance.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">{{ __('Rejected') }}</span></td>
                                    <td class="small">{{ __("Not accepted — check the reviewer's remarks for why.") }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">{{ __('Needs Revision') }}</span></td>
                                    <td class="small">{{ __('Close, but something needs fixing before it can be approved — just resubmit.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">{{ __('Closed') }}</span></td>
                                    <td class="small">{{ __('Finalized, no further action possible.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">{{ __('Cancelled') }}</span></td>
                                    <td class="small">{{ __('The task itself was called off.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-3">{{ __('Report Review Status') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm status-table">
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">{{ __('Pending') }}</span></td>
                                    <td class="small">{{ __('Submitted, waiting for a reviewer to look at it.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">{{ __('Re-submitted') }}</span></td>
                                    <td class="small">{{ __('A corrected version was sent back in after "Needs Revision".') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">{{ __('Approved') }}</span> / <span
                                            class="badge bg-success">{{ __('Approved with Remarks') }}</span></td>
                                    <td class="small">{{ __("Accepted — with remarks means there's a helpful note attached, but it still counts as approved.") }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">{{ __('Rejected') }}</span></td>
                                    <td class="small">{{ __('Not accepted.') }}</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">{{ __('Needs Revision') }}</span></td>
                                    <td class="small">{{ __('Sent back for a fix — the volunteer can resubmit.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="volunteer">
                    <h2><i class="bi bi-person-check"></i> 6. {{ __('If You Are a Volunteer') }}</h2>
                    <p class="urdu">Agar aap volunteer hain, to bas yeh steps follow karein:</p>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">1</div>
                        <div><strong>{{ __('Log in.') }}</strong> {{ __("You land on your Dashboard — it shows what's due today, upcoming meetings, and any announcements for you.") }}</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">2</div>
                        <div><strong>{{ __('Check "My Tasks" and "Meetings" / "Field Visits".') }}</strong> {{ __('Anything assigned to you — individually, or because your whole team/department/UC/NA was assigned — shows up here automatically. A task never requires a meeting to exist; treat each list separately.') }}
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">3</div>
                        <div><strong>{{ __('Do the work.') }}</strong> {{ __('Whatever the task or meeting asks for — a visit, a collection, a call, an event.') }}</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">4</div>
                        <div><strong>{{ __('Submit your report.') }}</strong> {{ __('Open the task and fill in: work summary, detailed description, achievements, problems faced, next plan, and working hours. If money was collected, enter the') }}
                            <strong>{{ __('amount') }}</strong> {{ __('and attach a photo/scan of the') }}
                            <strong>{{ __('receipt') }}</strong>. {{ __('You can attach more than one file.') }}</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">5</div>
                        <div><strong>{{ __('Track the status.') }}</strong> {{ __('Watch it move from "Report Submitted" to either "Approved" or "Needs Revision" (see the status table above). If it needs revision, read the reviewer\'s remarks, fix what they asked for, and resubmit — that becomes version 2.') }}
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="step-num">6</div>
                        <div><strong>{{ __('Everything else works the same way:') }}</strong> {{ __('My Leave, My Expenses, and My Documents are all "submit, then wait for a status update" — there\'s nothing extra to learn.') }}</div>
                    </div>
                </section>

                <section id="leader">
                    <h2><i class="bi bi-person-workspace"></i> 7. {{ __('If You Manage Others (Team Leader / NA Head / Admin)') }}
                    </h2>
                    <p class="urdu">Agar aap kisi team, department, UC ya NA ke zimmedar hain:</p>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">1</div>
                        <div><strong>{{ __('Create a Meeting or Task.') }}</strong> {{ __('From Meetings or Tasks, click "Add". Give it a title, priority/due date, and (for meetings) a date, time and location.') }}</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">2</div>
                        <div>
                            <strong>{{ __('Choose the audience ("Assign To").') }}</strong> {{ __("You'll always see the same seven options:") }}
                            <ul class="small mb-0 mt-1">
                                <li><strong>{{ __('Specific User(s)') }}</strong> — {{ __('hand-pick exactly who, one or many.') }}</li>
                                <li><strong>{{ __('Entire Team') }}</strong> — {{ __('everyone currently on that team.') }}</li>
                                <li><strong>{{ __('Entire Department') }}</strong> — {{ __("everyone in that department's teams, across") }}
                                    <em>{{ __('every') }}</em> {{ __('UC/NA (Department is shared, not tied to one UC).') }}</li>
                                <li><strong>{{ __('Entire UC') }}</strong> — {{ __('every active volunteer in that UC.') }}</li>
                                <li><strong>{{ __('Entire NA') }}</strong> — {{ __('every active volunteer across every UC in that NA.') }}</li>
                                <li><strong>{{ __('Multiple NAs') }}</strong> — {{ __('pick two or more NAs at once.') }}</li>
                                <li><strong>{{ __('All Volunteers') }}</strong> — {{ __('the whole organization.') }}</li>
                            </ul>
                            <span class="small text-muted">{{ __("Whichever you pick, the exact list of people is locked in the moment you save — someone joining the team later won't retroactively appear on an older meeting/task.") }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">3</div>
                        <div><strong>{{ __('Wait for reports to come in.') }}</strong> <strong>{{ __('Report Reviews') }}</strong> {{ __("lists every report currently waiting on your decision. A Team Leader only sees their own team's reports; an Admin/NA Head sees their whole NA's (every UC in it).") }}</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">4</div>
                        <div>
                            <strong>{{ __('Review each report') }}</strong> — {{ __('open it, read the summary and check any attached receipt, then pick one:') }}
                            <ul class="small mb-0 mt-1">
                                <li><strong>{{ __('Approve') }}</strong> — {{ __('work is accepted, done.') }}</li>
                                <li><strong>{{ __('Approve with Remarks') }}</strong> — {{ __('accepted, but you want to leave a note for the record.') }}</li>
                                <li><strong>{{ __('Reject') }}</strong> — {{ __('not accepted.') }}</li>
                                <li><strong>{{ __('Return for Revision') }}</strong> — {{ __('close, but needs a specific fix; the volunteer can resubmit.') }}</li>
                                <li><strong>{{ __('Request More Information') }}</strong> — {{ __('you need to ask something before deciding.') }}</li>
                            </ul>
                            {{ __('The volunteer is notified automatically, whichever you choose.') }}
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="step-num">5</div>
                        <div><strong>{{ __("Check your NA's health.") }}</strong> {{ __('Open') }} <strong>{{ __('NAs') }} &rarr; ({{ __('your NA') }})</strong> {{ __('for a dashboard of volunteers, meetings, attendance, tasks, reports, fund collection, and working hours — rolled up across every UC in that NA. Use') }} <strong>{{ __('NAs') }} &rarr; {{ __('Compare') }}</strong>
                            {{ __('to see how NAs stack up against each other — this exists purely to help planning, it is never a ranking of individual volunteers.') }}</div>
                    </div>
                </section>

                <section id="modules">
                    <h2><i class="bi bi-grid-3x3-gap"></i> 8. {{ __('Other Sections, Briefly') }}</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-megaphone"></i> {{ __('Projects') }}</h5>
                                <p class="small mb-0">{{ __('A larger initiative that groups several meetings/tasks under one Department, with its own progress %.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-broadcast"></i> {{ __('Announcements') }}</h5>
                                <p class="small mb-0">{{ __('One-way messages from leadership, sent to a Team, Department, UC, NA, or everyone.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-bullseye"></i> {{ __('Targets') }}</h5>
                                <p class="small mb-0">{{ __('A goal set for a person/team/UC/NA with a deadline, tracked to completion.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-airplane"></i> {{ __('Leave') }} &amp; <i
                                        class="bi bi-receipt"></i> {{ __('Expense Claims') }}</h5>
                                <p class="small mb-0">{{ __('A volunteer submits a request; their reviewer approves or rejects it — same pattern as task reports.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-folder"></i> {{ __('Documents') }}</h5>
                                <p class="small mb-0">{{ __("A volunteer's CNIC, certificates, or agreements — visible to them, their Team Leader/Reporting Head, and their NA's Admin.") }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-bar-chart-line"></i> {{ __('Performance') }}</h5>
                                <p class="small mb-0">{{ __('A personal, private summary of attendance, hours, and completed work — for internal review, not a public ranking.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-graph-up"></i> {{ __('Analytics') }}</h5>
                                <p class="small mb-0">{{ __('Org-wide charts for Admin/Super Admin — volunteer counts, task completion trends, report volume over time.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-search"></i> {{ __('Search') }}</h5>
                                <p class="small mb-0">{{ __('One search box that looks across NAs, UCs, Users, Tasks, Meetings, and Projects at once.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-ui-checks-grid"></i> {{ __('Forms') }}</h5>
                                <p class="small mb-0">{{ __('Custom fields an Admin can attach to a task instead of the standard report form — for structured data collection.') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="faq">
                    <h2><i class="bi bi-question-circle"></i> 9. {{ __('Frequently Asked Questions') }}</h2>

                    <div class="mb-3">
                        <strong>{{ __('Do I need a meeting to get a task?') }}</strong>
                        <p class="mb-0 small text-muted">{{ __('No. A task can be created and assigned entirely on its own, straight from the Tasks page — a meeting is completely optional.') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('What if I made a mistake in my report after submitting?') }}</strong>
                        <p class="mb-0 small text-muted">{{ __('If it\'s already reviewed and sent back as "Needs Revision", just edit and resubmit — it becomes a new version automatically. Nothing you submitted is ever deleted, every past version stays on record.') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Who can see my data?') }}</strong>
                        <p class="mb-0 small text-muted">{{ __("Only your Reporting Head, your Team Leader, your NA's Admin or NA Head, and Super Admin — strictly based on where you sit in the org chart in section 1. No one outside your chain can see your reports.") }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Is there a leaderboard or ranking of volunteers?') }}</strong>
                        <p class="mb-0 small text-muted">{{ __('No. Rankings only exist at the NA level, purely as a management tool to compare how NAs are performing — never individual volunteers against each other.') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __("Why isn't there a Provincial Assembly (PP) level?") }}</strong>
                        <p class="mb-0 small text-muted">{{ __('Islamabad Capital Territory is a federal territory, not part of any province, so it has no real Provincial Assembly constituencies — only NA (National Assembly) and UC (local government) exist. NA is the unit someone is actually put in charge of — and every UC inside it is their responsibility. UC is where volunteers, teams, and projects actually attach and do work. "Sector" is just an optional label on a UC, not a real level.') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __("What's the difference between a Meeting and a Task?") }}</strong>
                        <p class="mb-0 small text-muted">{{ __("A Meeting is a scheduled event with a date/time/location that people attend. A Task is a piece of work with a deadline that someone completes and reports back on. A meeting can spawn tasks, but a task doesn't need a meeting.") }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>{{ __('Can one task be assigned to more than one person?') }}</strong>
                        <p class="mb-0 small text-muted">{{ __('Yes — pick "Specific User(s)" and select several, or use "Entire Team"/"Entire Department"/"Entire UC"/"Entire NA"/"All Volunteers" to assign it to everyone in that group at once. Each assignee submits and tracks their own report separately.') }}</p>
                    </div>
                    <div>
                        <strong>{{ __("I'm a Team Leader — do I also get assigned tasks like a volunteer?") }}</strong>
                        <p class="mb-0 small text-muted">{{ __("Yes. A Team Leader has two hats: reviewing their team's reports, and also receiving and completing their own tasks/meetings just like any volunteer.") }}</p>
                    </div>
                </section>

            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>