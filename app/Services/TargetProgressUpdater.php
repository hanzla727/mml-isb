<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\Meeting;
use App\Models\Target;
use App\Models\TargetProgressUpdate;
use App\Models\User;
use Illuminate\Support\Carbon;

class TargetProgressUpdater
{
    /**
     * Recalculate progress for every active target that applies to the given
     * user, for the period containing $date. Recomputed from source data
     * (not incremented) so edits to past reports stay correct.
     */
    public function syncForUser(User $user, Carbon $date): void
    {
        $targets = Target::query()
            ->where('is_active', true)
            ->whereIn('metric', ['hours', 'meetings'])
            ->applicableTo($user)
            ->get();

        foreach ($targets as $target) {
            [$start, $end, $periodKey] = $this->periodBounds($target->type, $date);

            $value = $target->metric === 'hours'
                ? DailyReport::where('user_id', $user->id)->whereBetween('report_date', [$start, $end])->sum('total_hours')
                : Meeting::whereHas('dailyReport', fn ($q) => $q->where('user_id', $user->id)->whereBetween('report_date', [$start, $end]))->count();

            TargetProgressUpdate::updateOrCreate(
                ['target_id' => $target->id, 'user_id' => $user->id, 'period_key' => $periodKey],
                ['current_value' => $value]
            );
        }
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public function periodBounds(string $type, Carbon $date): array
    {
        return match ($type) {
            'daily' => [$date->copy()->startOfDay(), $date->copy()->endOfDay(), $date->format('Y-m-d')],
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek(), $date->format('o-\WW')],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth(), $date->format('Y-m')],
            'yearly' => [$date->copy()->startOfYear(), $date->copy()->endOfYear(), $date->format('Y')],
        };
    }

    /**
     * Attach each target's current-period progress value for $user as a
     * transient `current_value` attribute (used by TargetResource and the
     * web dashboards to show live progress without an extra round trip).
     *
     * @param  \Illuminate\Support\Collection<int, Target>  $targets
     */
    public function attachCurrentProgress($targets, User $user): void
    {
        $targets->each(function (Target $target) use ($user) {
            [, , $periodKey] = $this->periodBounds($target->type, Carbon::today());

            $progress = $target->progressUpdates()
                ->where('user_id', $user->id)
                ->where('period_key', $periodKey)
                ->first();

            $target->setAttribute('current_value', $progress?->current_value ?? 0);
            $target->setAttribute('notes', $progress?->notes);
            $target->setAttribute('is_completed', $progress?->is_completed ?? false);
        });
    }

    /**
     * Record a volunteer's manual self-report against an assigned target for
     * the period containing $date. For `hours`/`meetings` metric targets the
     * numeric value is intentionally NOT overwritten here — it's derived from
     * real report/meeting data by syncForUser() — only `notes`/`is_completed`
     * are persisted so a volunteer can't inflate their own auto-tracked numbers.
     * `custom` metric targets have no other source of truth, so current_value
     * is accepted as given.
     */
    public function recordManualProgress(User $user, Target $target, Carbon $date, array $data): void
    {
        [, , $periodKey] = $this->periodBounds($target->type, $date);

        $update = [
            'notes' => $data['notes'] ?? null,
            'is_completed' => $data['is_completed'] ?? false,
        ];

        if ($target->metric === 'custom') {
            $update['current_value'] = $data['current_value'] ?? 0;
        }

        TargetProgressUpdate::updateOrCreate(
            ['target_id' => $target->id, 'user_id' => $user->id, 'period_key' => $periodKey],
            $update
        );
    }
}
