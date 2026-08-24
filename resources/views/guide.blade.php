<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>How This System Works - {{ config('app.name') }}</title>
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
            <span class="navbar-brand"><i class="bi bi-heart-fill"></i> Volunteer Mgmt &mdash; User Guide</span>
            <a href="{{ url('/') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back to
                Dashboard</a>
        </div>
    </nav>

    <div class="guide-body">

        <div class="text-center py-5">
            <h1 class="fw-bold">How This System Works</h1>
            <p class="text-muted fs-5">A complete, easy-to-follow guide &mdash; from the big picture down to exactly
                what to click.</p>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="quickstart-card">
                    <h5><i class="bi bi-person"></i> New Volunteer? Start Here</h5>
                    <ol class="small mb-0 ps-3">
                        <li>Log in with the email/password you were given.</li>
                        <li>Open <strong>My Tasks</strong> and <strong>Meetings</strong> &mdash; that's everything
                            assigned to you.</li>
                        <li>When you finish something, open it and <strong>submit a report</strong>.</li>
                    </ol>
                </div>
            </div>
            <div class="col-md-6">
                <div class="quickstart-card">
                    <h5><i class="bi bi-person-workspace"></i> New Admin / Team Leader? Start Here</h5>
                    <ol class="small mb-0 ps-3">
                        <li>Log in &mdash; you land on the <strong>Dashboard</strong>.</li>
                        <li>Create a <strong>Meeting</strong> or <strong>Task</strong> and pick who it's for.</li>
                        <li>Watch <strong>Report Reviews</strong> for work that needs your decision.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 d-none d-lg-block">
                <div class="guide-toc">
                    <div class="text-uppercase text-muted small fw-semibold mb-2 px-2">On this page</div>
                    <a href="#structure">1. Organization Structure</a>
                    <a href="#roles">2. Roles &amp; Your Menu</a>
                    <a href="#glossary">3. Glossary</a>
                    <a href="#story">4. A Real Example, Start to Finish</a>
                    <a href="#statuses">5. What Every Status Means</a>
                    <a href="#volunteer">6. If You Are a Volunteer</a>
                    <a href="#leader">7. If You Manage Others</a>
                    <a href="#modules">8. Other Sections</a>
                    <a href="#faq">9. Frequently Asked Questions</a>
                </div>
            </div>

            <div class="col-lg-9">

                <section id="structure">
                    <h2><i class="bi bi-diagram-3"></i> 1. Organization Structure</h2>
                    <p>This organization works within <strong>Islamabad only</strong>. Islamabad Capital Territory has
                        no Provincial Assembly &mdash; it's a federal territory, not part of any province &mdash; so the
                        real structure here is just two levels: <strong>NA &rarr; UC</strong>. Everyone in the system
                        sits somewhere in this chain, and every volunteer has all levels set (NA is automatic,
                        UC/Department/Team are explicit) &mdash; none of them are optional.</p>
                    <p class="urdu">Yeh organization sirf Islamabad ke andar kaam karti hai. Islamabad me Provincial
                        Assembly nahi hoti, isliye asal structure sirf do level ka hai: NA &rarr; UC. Har volunteer ka
                        UC, Department, Team aur Reporting Head zaroor set hota hai.</p>

                    <div class="flow-row my-4">
                        <div class="flow-box"><strong><i class="bi bi-building"></i> Organization</strong><small>The
                                whole organization</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-map"></i> NA</strong><small>e.g. NA-48 &mdash;
                                who's assigned</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-pin-map"></i> UC</strong><small>e.g. UC F-10
                                &mdash; where work happens</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-people-fill"></i> Team</strong><small>e.g. Donor
                                Relations Team</small></div>
                        <div class="flow-arrow"><i class="bi bi-arrow-right d-none d-md-inline"></i><i
                                class="bi bi-arrow-down d-md-none"></i></div>
                        <div class="flow-box"><strong><i class="bi bi-person"></i> Volunteer</strong><small>Does the
                                actual field work</small></div>
                    </div>

                    <div class="mockup mb-3">
                        <div class="mockup-title"><i class="bi bi-signpost-split"></i> What each level actually means
                        </div>
                        <ul class="small mb-0 ps-3">
                            <li><strong>NA</strong> is the unit a person is actually put in charge of (its <strong>NA
                                    Head</strong>). Whatever UCs sit under that NA, all of them are that person's
                                responsibility &mdash; that's the whole point of the NA level.</li>
                            <li><strong>UC</strong> is where the real, on-the-ground work lives: Teams, Volunteers, and
                                Projects all attach to a UC. <strong>Sector</strong> (e.g. "F-10") is just an optional,
                                informal label you can put on a UC &mdash; it doesn't add another level, it's not
                                required, and nothing in the system depends on it.</li>
                        </ul>
                    </div>

                    <div class="mockup mb-3">
                        <div class="mockup-title"><i class="bi bi-diagram-3"></i> Department is shared across every
                            NA/UC</div>
                        <p class="mb-2 small">Department (Fundraising, Hospital, Mosque, Khidmat, Dawah, Administration,
                            ...) is <strong>not</strong> part of the NA &rarr; UC chain above &mdash; it's the same
                            org-wide list everywhere. A Team is what actually connects a Department to one specific UC.
                            So the same "Fundraising" department can have a team in UC F-10 <em>and</em> a separate team
                            in UC G-9 &mdash; it's one shared category, used in more than one place.</p>
                        <div class="mockup-title"><i class="bi bi-signpost-split"></i> A real example from this system
                        </div>
                        <strong>NA-48</strong> (run by <em>NA Head One</em>) has two UCs: <strong>UC F-10</strong> and
                        <strong>UC F-11</strong>.
                        <strong>UC F-10</strong> has <strong>Donor Relations Team</strong> (Team, under the
                        <em>Fundraising</em> department, led by <em>Team Leader One</em>) &rarr;
                        <strong>Volunteer One</strong> (a volunteer on that team).
                        Meanwhile <strong>NA-49</strong> has its own <strong>UC G-9</strong>, with a <strong>Community
                            Fundraising Team</strong> &mdash; also under <em>Fundraising</em>.
                    </div>

                    <p class="mb-1"><strong>Reporting Head</strong> is separate from the team/department chart. It's the
                        one specific person answerable for a volunteer &mdash; usually their Team Leader, but it can be
                        set to anyone (an NA Head, for a volunteer with no team leader yet). This is who gets notified
                        first when that volunteer's work needs a decision.</p>
                    <p class="mb-0"><strong>Multiple NAs:</strong> an Admin can be responsible for more than one NA at
                        once (e.g. both NA-48 and NA-49). An NA Head is always scoped to exactly one NA &mdash; but
                        every UC in it.</p>
                </section>

                <section id="roles">
                    <h2><i class="bi bi-person-badge"></i> 2. Roles &amp; Your Menu</h2>
                    <p>There are 5 roles. Each one only sees what's relevant to them &mdash; the menu on the left (or
                        top, if you're a volunteer) changes automatically based on your role.</p>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-shield-lock"></i> Super Admin</h5>
                                <p class="small mb-2">Sees and manages every NA/UC, every user, every setting. Usually
                                    just one or two people at the top of the organization.</p>
                                <p class="small text-muted mb-0"><strong>Example:</strong> Super Admin</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-shield"></i> Admin</h5>
                                <p class="small mb-2">Manages one or more assigned NAs: creates meetings/tasks, reviews
                                    reports, manages users, departments and teams &mdash; but only within their own
                                    NA(s).</p>
                                <p class="small text-muted mb-0"><strong>Example:</strong> Admin One &rarr; manages
                                    NA-48</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-geo-alt"></i> NA Head</h5>
                                <p class="small mb-2">Same abilities as Admin, but always scoped to exactly one NA
                                    &mdash; and every UC underneath it &mdash; the one they were appointed to lead.</p>
                                <p class="small text-muted mb-0"><strong>Example:</strong> NA Head One &rarr; leads
                                    NA-48 (both UC F-10 and UC F-11)</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5><i class="bi bi-people-fill"></i> Team Leader</h5>
                                <p class="small mb-2">Runs one Team. Reviews that team's daily reports first, sees the
                                    team's tasks/meetings, and also has their own personal tasks like a volunteer does.
                                </p>
                                <p class="small text-muted mb-0"><strong>Example:</strong> Team Leader One &rarr; leads
                                    Donor Relations Team</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="role-card">
                                <h5><i class="bi bi-person"></i> Volunteer</h5>
                                <p class="small mb-2">Sees only their own meetings, tasks, reports, targets, and
                                    leave/expense history. This is the majority of the organization.</p>
                                <p class="small text-muted mb-0"><strong>Example:</strong> Volunteer One &rarr; on Donor
                                    Relations Team, reports to Team Leader One</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="menu-preview">
                                <div class="fw-semibold mb-2 text-white"><i class="bi bi-list"></i> Sidebar &mdash;
                                    Admin / NA Head / Team Leader</div>
                                <div class="mp-item mp-active">Dashboard</div>
                                <div class="mp-item">Search</div>
                                <div class="mp-item">NAs <span class="text-muted">(&plus; UCs, super admin &amp; NA
                                        managers)</span></div>
                                <div class="mp-item">Reports &middot; Meetings &middot; Tasks &middot; Report Reviews
                                    &middot; Projects</div>
                                <div class="mp-item">Users &middot; Departments &middot; Teams &middot; My Team</div>
                                <div class="mp-item">Leave Requests &middot; Expense Claims</div>
                                <div class="mp-item">Announcements &middot; Targets &middot; Analytics &middot;
                                    Performance &middot; Forms</div>
                                <div class="mp-item">How It Works</div>
                            </div>
                            <p class="small text-muted mt-1 mb-0">Some items only appear if your role has that specific
                                permission &mdash; e.g. a Team Leader won't see "Users" or "NAs".</p>
                        </div>
                        <div class="col-md-6">
                            <div class="menu-preview">
                                <div class="fw-semibold mb-2 text-white"><i class="bi bi-list"></i> Top Menu &mdash;
                                    Volunteer</div>
                                <div class="mp-item mp-active">Dashboard</div>
                                <div class="mp-item">My Reports &middot; Field Visits &middot; Meetings</div>
                                <div class="mp-item">My Tasks &middot; My Targets</div>
                                <div class="mp-item">Announcements &middot; My Progress &middot; My Performance</div>
                                <div class="mp-item">My Leave &middot; My Expenses &middot; My Documents</div>
                                <div class="mp-item">How It Works</div>
                            </div>
                            <p class="small text-muted mt-1 mb-0">Everything here is scoped to "my" &mdash; a volunteer
                                never sees anyone else's data.</p>
                        </div>
                    </div>
                </section>

                <section id="glossary">
                    <h2><i class="bi bi-book"></i> 3. Glossary &mdash; Every Term, Explained</h2>
                    <p>Click any term to expand it. Come back here any time something is unclear.</p>

                    <div class="accordion" id="glossaryAccordion">
                        @php
                            $terms = [
                                ['NA', 'The unit a person is actually assigned to manage (e.g. "NA-48"), via an "NA Head". Every UC under that NA is that person\'s responsibility. It is the real top-level structure here &mdash; Islamabad has no Provincial Assembly, so there is no level above NA.'],
                                ['UC', 'The bottom-most operational unit (e.g. "UC F-10") &mdash; this is where Teams, Projects, and Volunteers actually attach. One NA can have several UCs.'],
                                ['Sector', 'A purely optional, informal label you can put on a UC (e.g. "F-10"). It is not a structural level of its own and nothing depends on it.'],
                                ['Department', 'A shared, org-wide function, e.g. "Fundraising" or "Hospital" — the same list applies to every UC/NA, it is never specific to just one. To give a department a presence in a specific UC, you create a Team for it there.'],
                                ['Team', 'The UC-specific instance of a Department — e.g. "Donor Relations Team" is Fundraising\'s team in UC F-10. Led by one Team Leader. The same Department can have different Teams in different UCs.'],
                                ['Reporting Head', 'The one specific person responsible for a volunteer. Usually the Team Leader, but can be reassigned to anyone (e.g. an NA Head).'],
                                ['Project', 'A larger initiative that groups several meetings and tasks together under one name, with its own progress percentage. Belongs to one Department and one UC at the same time (e.g. a Fundraising project running specifically in UC F-10).'],
                                ['Meeting', 'A scheduled event (date, time, location, agenda) with invited participants. Tasks can optionally be linked to a meeting, but don\'t have to be.'],
                                ['Task', 'A specific piece of work assigned to one or more people, with a priority and due date. Can exist completely on its own, with no meeting attached.'],
                                ['Scope / Audience', 'Who a meeting or task is for. One of: a specific person or people, an entire Team, an entire Department, an entire UC, an entire NA, several NAs at once, or every volunteer in the organization.'],
                                ['Report', 'What a volunteer submits after doing the work &mdash; a written summary, working hours, amount collected (if any), and file attachments like receipts.'],
                                ['Version', 'Every time a report is sent back and resubmitted, it becomes a new version (v1, v2, v3...). Nothing is ever deleted or overwritten.'],
                                ['Review', 'The decision a reviewer makes on a submitted report: Approve, Approve with Remarks, Reject, Return for Revision, or Request More Information.'],
                                ['Attachment', 'Any file uploaded with a report &mdash; a receipt, a photo, a document.'],
                                ['Target', 'A goal set for a person, team, UC, or NA with a deadline, tracked until it\'s met.'],
                                ['Announcement', 'A one-way message from leadership to a Team, Department, UC, NA, or everyone.'],
                                ['NA Dashboard', 'A single screen showing an NA\'s health: volunteers, meetings, attendance, tasks, reports, fund collection, and working hours.'],
                                ['NA Comparison / Ranking', 'A side-by-side comparison of NAs against each other, for management planning only &mdash; not a leaderboard, and never about individual volunteers.'],
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
                    <h2><i class="bi bi-arrow-repeat"></i> 4. A Real Example, Start to Finish</h2>
                    <p>Here is the exact same journey a real task takes in this system, told as a short story. This is
                        the one flow everything else supports.</p>
                    <p class="urdu">Yeh ek chota sa real example hai jo dikhata hai ke ek task shuru se end tak kaise
                        chalta hai.</p>

                    <div class="mt-4">
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-1-circle-fill"></i> Admin One (Admin, NA-48) creates a task
                            </div>
                            Opens <strong>Tasks &rarr; Add Task</strong>. Title: "Collect donations after Friday
                            prayer". Priority: Medium. Under "Assign To" picks scope <strong>Entire Team</strong> and
                            selects <strong>Donor Relations Team</strong>. No meeting is attached &mdash; this task
                            stands on its own. Saves it.
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-2-circle-fill"></i> The system assigns it instantly</div>
                            Every current member of Donor Relations Team &mdash; including <strong>Volunteer
                                One</strong> &mdash; is attached to the task right away and gets a notification. The
                            task's status becomes <span class="badge bg-info text-dark">Assigned</span>.
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-3-circle-fill"></i> Volunteer One logs in</div>
                            Opens <strong>My Tasks</strong>, sees "Collect donations after Friday prayer" waiting, and
                            goes to do the actual collection at the mosque.
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-4-circle-fill"></i> Volunteer One submits a report</div>
                            Opens the task, fills in <em>Work Summary</em> ("Collected donations after Friday prayer"),
                            <em>Working Hours</em> (2), <em>Amount Collected</em> (Rs. 5,000), and uploads a photo of
                            the receipt as an attachment. Clicks <strong>Submit Report</strong>. Task status becomes
                            <span class="badge bg-info text-dark">Report Submitted</span>.
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-5-circle-fill"></i> Team Leader One reviews it first</div>
                            Because Team Leader One leads Donor Relations Team, the report lands in their <strong>Report
                                Reviews</strong> queue. They open it, read the summary, see the receipt, and check the
                            amount matches. They approve it.
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-6-circle-fill"></i> It's done</div>
                            The report becomes <span class="badge bg-success">Approved</span> and the task becomes <span
                                class="badge bg-success">Approved</span>. Volunteer One gets notified. The Rs. 5,000 is
                            now counted in <strong>NA-48's</strong> Dashboard under fund collection.
                        </div>
                        <div class="story-msg">
                            <div class="who"><i class="bi bi-exclamation-circle-fill"></i> If instead it needed a
                                fix&hellip;</div>
                            Team Leader One could have chosen <strong>Return for Revision</strong> with a remark like
                            "please attach a clearer receipt photo". The task would show <span
                                class="badge bg-warning text-dark">Needs Revision</span>, Volunteer One would see the
                            remark, fix it, and resubmit &mdash; creating version 2 of the report. Nothing from version
                            1 is lost.
                        </div>
                    </div>
                </section>

                <section id="statuses">
                    <h2><i class="bi bi-flag"></i> 5. What Every Status Means</h2>
                    <p>Statuses can look intimidating at first glance &mdash; here's every one of them in plain
                        language.</p>

                    <h6 class="mt-3">Task Status</h6>
                    <div class="table-responsive">
                        <table class="table table-sm status-table">
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-secondary">Not Assigned</span></td>
                                    <td class="small">Created but nobody is attached to it yet.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">Assigned</span></td>
                                    <td class="small">Someone (or a whole team/department/UC/NA) has been given this
                                        task.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">In Progress</span></td>
                                    <td class="small">Work has started.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Waiting for Information</span></td>
                                    <td class="small">The reviewer asked a question before deciding &mdash; check the
                                        remarks.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">Report Submitted</span></td>
                                    <td class="small">The volunteer has submitted their report; it's now waiting on a
                                        reviewer.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">Under Review</span></td>
                                    <td class="small">A reviewer is actively looking at it.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">Approved</span></td>
                                    <td class="small">Done and accepted &mdash; counted in dashboards and NA
                                        performance.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Rejected</span></td>
                                    <td class="small">Not accepted &mdash; check the reviewer's remarks for why.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Needs Revision</span></td>
                                    <td class="small">Close, but something needs fixing before it can be approved
                                        &mdash; just resubmit.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Closed</span></td>
                                    <td class="small">Finalized, no further action possible.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Cancelled</span></td>
                                    <td class="small">The task itself was called off.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-3">Report Review Status</h6>
                    <div class="table-responsive">
                        <table class="table table-sm status-table">
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="small">Submitted, waiting for a reviewer to look at it.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info text-dark">Re-submitted</span></td>
                                    <td class="small">A corrected version was sent back in after "Needs Revision".</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">Approved</span> / <span
                                            class="badge bg-success">Approved with Remarks</span></td>
                                    <td class="small">Accepted &mdash; with remarks means there's a helpful note
                                        attached, but it still counts as approved.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">Rejected</span></td>
                                    <td class="small">Not accepted.</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">Needs Revision</span></td>
                                    <td class="small">Sent back for a fix &mdash; the volunteer can resubmit.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="volunteer">
                    <h2><i class="bi bi-person-check"></i> 6. If You Are a Volunteer</h2>
                    <p class="urdu">Agar aap volunteer hain, to bas yeh steps follow karein:</p>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">1</div>
                        <div><strong>Log in.</strong> You land on your Dashboard &mdash; it shows what's due today,
                            upcoming meetings, and any announcements for you.</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">2</div>
                        <div><strong>Check "My Tasks" and "Meetings" / "Field Visits".</strong> Anything assigned to you
                            &mdash; individually, or because your whole team/department/UC/NA was assigned &mdash; shows
                            up here automatically. A task never requires a meeting to exist; treat each list separately.
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">3</div>
                        <div><strong>Do the work.</strong> Whatever the task or meeting asks for &mdash; a visit, a
                            collection, a call, an event.</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">4</div>
                        <div><strong>Submit your report.</strong> Open the task and fill in: work summary, detailed
                            description, achievements, problems faced, next plan, and working hours. If money was
                            collected, enter the <strong>amount</strong> and attach a photo/scan of the
                            <strong>receipt</strong>. You can attach more than one file.</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">5</div>
                        <div><strong>Track the status.</strong> Watch it move from "Report Submitted" to either
                            "Approved" or "Needs Revision" (see the status table above). If it needs revision, read the
                            reviewer's remarks, fix what they asked for, and resubmit &mdash; that becomes version 2.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="step-num">6</div>
                        <div><strong>Everything else works the same way:</strong> My Leave, My Expenses, and My
                            Documents are all "submit, then wait for a status update" &mdash; there's nothing extra to
                            learn.</div>
                    </div>
                </section>

                <section id="leader">
                    <h2><i class="bi bi-person-workspace"></i> 7. If You Manage Others (Team Leader / NA Head / Admin)
                    </h2>
                    <p class="urdu">Agar aap kisi team, department, UC ya NA ke zimmedar hain:</p>

                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">1</div>
                        <div><strong>Create a Meeting or Task.</strong> From Meetings or Tasks, click "Add". Give it a
                            title, priority/due date, and (for meetings) a date, time and location.</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">2</div>
                        <div>
                            <strong>Choose the audience ("Assign To").</strong> You'll always see the same seven
                            options:
                            <ul class="small mb-0 mt-1">
                                <li><strong>Specific User(s)</strong> &mdash; hand-pick exactly who, one or many.</li>
                                <li><strong>Entire Team</strong> &mdash; everyone currently on that team.</li>
                                <li><strong>Entire Department</strong> &mdash; everyone in that department's teams,
                                    across <em>every</em> UC/NA (Department is shared, not tied to one UC).</li>
                                <li><strong>Entire UC</strong> &mdash; every active volunteer in that UC.</li>
                                <li><strong>Entire NA</strong> &mdash; every active volunteer across every UC in that
                                    NA.</li>
                                <li><strong>Multiple NAs</strong> &mdash; pick two or more NAs at once.</li>
                                <li><strong>All Volunteers</strong> &mdash; the whole organization.</li>
                            </ul>
                            <span class="small text-muted">Whichever you pick, the exact list of people is locked in the
                                moment you save &mdash; someone joining the team later won't retroactively appear on an
                                older meeting/task.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">3</div>
                        <div><strong>Wait for reports to come in.</strong> <strong>Report Reviews</strong> lists every
                            report currently waiting on your decision. A Team Leader only sees their own team's reports;
                            an Admin/NA Head sees their whole NA's (every UC in it).</div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-num">4</div>
                        <div>
                            <strong>Review each report</strong> &mdash; open it, read the summary and check any attached
                            receipt, then pick one:
                            <ul class="small mb-0 mt-1">
                                <li><strong>Approve</strong> &mdash; work is accepted, done.</li>
                                <li><strong>Approve with Remarks</strong> &mdash; accepted, but you want to leave a note
                                    for the record.</li>
                                <li><strong>Reject</strong> &mdash; not accepted.</li>
                                <li><strong>Return for Revision</strong> &mdash; close, but needs a specific fix; the
                                    volunteer can resubmit.</li>
                                <li><strong>Request More Information</strong> &mdash; you need to ask something before
                                    deciding.</li>
                            </ul>
                            The volunteer is notified automatically, whichever you choose.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="step-num">5</div>
                        <div><strong>Check your NA's health.</strong> Open <strong>NAs &rarr; (your NA)</strong> for a
                            dashboard of volunteers, meetings, attendance, tasks, reports, fund collection, and working
                            hours &mdash; rolled up across every UC in that NA. Use <strong>NAs &rarr; Compare</strong>
                            to see how NAs stack up against each other &mdash; this exists purely to help planning, it
                            is never a ranking of individual volunteers.</div>
                    </div>
                </section>

                <section id="modules">
                    <h2><i class="bi bi-grid-3x3-gap"></i> 8. Other Sections, Briefly</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-megaphone"></i> Projects</h5>
                                <p class="small mb-0">A larger initiative that groups several meetings/tasks under one
                                    Department, with its own progress %.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-broadcast"></i> Announcements</h5>
                                <p class="small mb-0">One-way messages from leadership, sent to a Team, Department, UC,
                                    NA, or everyone.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-bullseye"></i> Targets</h5>
                                <p class="small mb-0">A goal set for a person/team/UC/NA with a deadline, tracked to
                                    completion.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-airplane"></i> Leave &amp; <i
                                        class="bi bi-receipt"></i> Expense Claims</h5>
                                <p class="small mb-0">A volunteer submits a request; their reviewer approves or rejects
                                    it &mdash; same pattern as task reports.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-folder"></i> Documents</h5>
                                <p class="small mb-0">A volunteer's CNIC, certificates, or agreements &mdash; visible to
                                    them, their Team Leader/Reporting Head, and their NA's Admin.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-bar-chart-line"></i> Performance</h5>
                                <p class="small mb-0">A personal, private summary of attendance, hours, and completed
                                    work &mdash; for internal review, not a public ranking.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-graph-up"></i> Analytics</h5>
                                <p class="small mb-0">Org-wide charts for Admin/Super Admin &mdash; volunteer counts,
                                    task completion trends, report volume over time.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-search"></i> Search</h5>
                                <p class="small mb-0">One search box that looks across NAs, UCs, Users, Tasks, Meetings,
                                    and Projects at once.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="role-card">
                                <h5 class="fs-6"><i class="bi bi-ui-checks-grid"></i> Forms</h5>
                                <p class="small mb-0">Custom fields an Admin can attach to a task instead of the
                                    standard report form &mdash; for structured data collection.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="faq">
                    <h2><i class="bi bi-question-circle"></i> 9. Frequently Asked Questions</h2>

                    <div class="mb-3">
                        <strong>Do I need a meeting to get a task?</strong>
                        <p class="mb-0 small text-muted">No. A task can be created and assigned entirely on its own,
                            straight from the Tasks page &mdash; a meeting is completely optional.</p>
                    </div>
                    <div class="mb-3">
                        <strong>What if I made a mistake in my report after submitting?</strong>
                        <p class="mb-0 small text-muted">If it's already reviewed and sent back as "Needs Revision",
                            just edit and resubmit &mdash; it becomes a new version automatically. Nothing you submitted
                            is ever deleted, every past version stays on record.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Who can see my data?</strong>
                        <p class="mb-0 small text-muted">Only your Reporting Head, your Team Leader, your NA's Admin or
                            NA Head, and Super Admin &mdash; strictly based on where you sit in the org chart in section
                            1. No one outside your chain can see your reports.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Is there a leaderboard or ranking of volunteers?</strong>
                        <p class="mb-0 small text-muted">No. Rankings only exist at the NA level, purely as a management
                            tool to compare how NAs are performing &mdash; never individual volunteers against each
                            other.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Why isn't there a Provincial Assembly (PP) level?</strong>
                        <p class="mb-0 small text-muted">Islamabad Capital Territory is a federal territory, not part of
                            any province, so it has no real Provincial Assembly constituencies &mdash; only NA (National
                            Assembly) and UC (local government) exist. NA is the unit someone is actually put in charge
                            of &mdash; and every UC inside it is their responsibility. UC is where volunteers, teams,
                            and projects actually attach and do work. "Sector" is just an optional label on a UC, not a
                            real level.</p>
                    </div>
                    <div class="mb-3">
                        <strong>What's the difference between a Meeting and a Task?</strong>
                        <p class="mb-0 small text-muted">A Meeting is a scheduled event with a date/time/location that
                            people attend. A Task is a piece of work with a deadline that someone completes and reports
                            back on. A meeting can spawn tasks, but a task doesn't need a meeting.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Can one task be assigned to more than one person?</strong>
                        <p class="mb-0 small text-muted">Yes &mdash; pick "Specific User(s)" and select several, or use
                            "Entire Team"/"Entire Department"/"Entire UC"/"Entire NA"/"All Volunteers" to assign it to
                            everyone in that group at once. Each assignee submits and tracks their own report
                            separately.</p>
                    </div>
                    <div>
                        <strong>I'm a Team Leader &mdash; do I also get assigned tasks like a volunteer?</strong>
                        <p class="mb-0 small text-muted">Yes. A Team Leader has two hats: reviewing their team's
                            reports, and also receiving and completing their own tasks/meetings just like any volunteer.
                        </p>
                    </div>
                </section>

            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>