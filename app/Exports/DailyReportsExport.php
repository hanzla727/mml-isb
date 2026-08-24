<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyReportsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $reports)
    {
    }

    public function collection(): Collection
    {
        return $this->reports;
    }

    public function headings(): array
    {
        return ['Date', 'Volunteer', 'Start Time', 'End Time', 'Total Hours', 'Meetings', 'Summary'];
    }

    public function map($report): array
    {
        return [
            $report->report_date->toDateString(),
            $report->user->name,
            $report->field_start_time,
            $report->field_end_time,
            $report->total_hours,
            $report->meetings_count ?? $report->meetings->count(),
            $report->summary,
        ];
    }
}
