<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\RecurrenceGenerator;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring';

    protected $description = 'Extend the generated occurrences for every recurring task template up to the horizon window';

    public function handle(RecurrenceGenerator $generator): int
    {
        $templates = Task::query()->whereNotNull('recurrence_rule')->get();

        $created = 0;
        foreach ($templates as $template) {
            $created += $generator->generateUpcoming($template)->count();
        }

        $this->info("Generated {$created} upcoming task occurrence(s) across {$templates->count()} recurring template(s).");

        return self::SUCCESS;
    }
}
