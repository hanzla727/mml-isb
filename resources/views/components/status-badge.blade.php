@php
    $status ??= null;
    $tone = match ($status) {
        'approved', 'approved_with_remarks', 'completed', 'closed', 'present', 'active', 'success' => 'success',
        'rejected', 'cancelled', 'overdue', 'absent', 'critical', 'danger' => 'danger',
        'needs_revision', 'waiting_for_information', 'pending', 'pending_review', 'late', 'planning', 'high' => 'warning',
        'submitted', 'assigned', 'in_progress', 're_submitted', 'under_review', 'upcoming', 'ongoing', 'medium' => 'info',
        default => 'secondary',
    };
    $textDark = in_array($tone, ['warning', 'info'], true);
@endphp
<span {{ $attributes->merge(['class' => 'badge bg-'.$tone.($textDark ? ' text-dark' : '')]) }}>
    {{ $label ?? str_replace('_', ' ', ucfirst($status ?? '—')) }}
</span>
