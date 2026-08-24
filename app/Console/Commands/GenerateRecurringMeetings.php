<?php

namespace App\Console\Commands;

use App\Models\ScheduledMeeting;
use App\Services\RecurrenceGenerator;
use Illuminate\Console\Command;

class GenerateRecurringMeetings extends Command
{
    protected $signature = 'meetings:generate-recurring';

    protected $description = 'Extend the generated occurrences for every recurring meeting template up to the horizon window';

    public function handle(RecurrenceGenerator $generator): int
    {
        $templates = ScheduledMeeting::query()->whereNotNull('recurrence_rule')->get();

        $created = 0;
        foreach ($templates as $template) {
            $created += $generator->generateUpcoming($template)->count();
        }

        $this->info("Generated {$created} upcoming meeting occurrence(s) across {$templates->count()} recurring template(s).");

        return self::SUCCESS;
    }
}
