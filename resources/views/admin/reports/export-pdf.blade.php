<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<h3>Daily Reports</h3>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Volunteer</th>
            <th>Start</th>
            <th>End</th>
            <th>Hours</th>
            <th>Meetings</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $report)
            <tr>
                <td>{{ $report->report_date->toDateString() }}</td>
                <td>{{ $report->user->name }}</td>
                <td>{{ $report->field_start_time }}</td>
                <td>{{ $report->field_end_time }}</td>
                <td>{{ $report->total_hours }}</td>
                <td>{{ $report->meetings_count ?? $report->meetings->count() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
