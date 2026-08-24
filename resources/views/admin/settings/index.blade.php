@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="card stat-card p-4" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Organization Name</label>
                <input type="text" name="organization_name" value="{{ old('organization_name', $settings['organization_name'] ?? '') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Organization Email</label>
                <input type="email" name="organization_email" value="{{ old('organization_email', $settings['organization_email'] ?? '') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Daily Report Reminder Time</label>
                <input type="time" name="daily_report_reminder_time" value="{{ old('daily_report_reminder_time', $settings['daily_report_reminder_time'] ?? '') }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Missed Report Grace Period (hours)</label>
                <input type="number" name="missed_report_grace_hours" value="{{ old('missed_report_grace_hours', $settings['missed_report_grace_hours'] ?? '') }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
@endsection
