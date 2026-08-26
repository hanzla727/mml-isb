<div>
    <div class="card stat-card filter-bar p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3 col-lg-2">
                <label class="form-label small text-muted">Volunteer</label>
                <select wire:model.live="userId" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small text-muted">Department</label>
                <select wire:model.live="departmentId" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small text-muted">Status</label>
                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small text-muted">Review Status</label>
                <select wire:model.live="reviewStatus" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending_review">Pending Review</option>
                    <option value="under_review">Under Review</option>
                    <option value="needs_revision">Needs Revision</option>
                    <option value="re_submitted">Re-submitted</option>
                    <option value="approved">Approved</option>
                    <option value="approved_with_remarks">Approved w/ Remarks</option>
                    <option value="rejected">Rejected</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>

        <div class="row g-2 align-items-end mt-1">
            <div class="col-md-3 col-lg-2">
                <label class="form-label small text-muted">From</label>
                <input type="date" wire:model.live="from" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small text-muted">To</label>
                <input type="date" wire:model.live="to" class="form-control form-control-sm">
            </div>
            <div class="col-md-6 col-lg-8 d-flex justify-content-end gap-2">
                @if ($this->hasActiveFilters)
                    <button type="button" wire:click="resetFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear filters
                    </button>
                @endif
                <a href="{{ route('admin.reports.export', [...$this->exportParams, 'format' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-filetype-csv"></i> CSV
                </a>
                <a href="{{ route('admin.reports.export', [...$this->exportParams, 'format' => 'pdf']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-filetype-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card stat-card" wire:loading.class="is-loading-target" wire:target="userId,departmentId,status,reviewStatus,from,to,resetFilters">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Volunteer</th>
                    <th>Department</th>
                    <th>Hours</th>
                    <th title="Field visits logged in each report">Meetings (Visits)</th>
                    <th>Status</th>
                    <th>Review</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr wire:key="report-{{ $report->id }}">
                        <td>{{ $report->report_date->toDateString() }}</td>
                        <td>{{ $report->user->name }}</td>
                        <td class="text-muted small">{{ $report->user->department?->name ?? '—' }}</td>
                        <td>{{ $report->total_hours }}</td>
                        <td>{{ $report->meetings_count }}</td>
                        <td><x-status-badge :status="$report->status" /></td>
                        <td>
                            @if ($report->review_status)
                                <x-status-badge :status="$report->review_status" />
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-journal-x"></i>
                                No reports match these filters.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $reports->links() }}</div>
</div>
