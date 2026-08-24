<?php

return [
    /*
     * Weights used by NaPerformanceService::score() to build the NA
     * Ranking composite score. Each sub-score is normalized to 0-100 before
     * weighting, so these just need to sum to 1.0 — adjust here to change
     * what "good performance" means without touching any code.
     */
    'ranking_weights' => [
        'task_completion_rate' => 0.35,
        'report_submission_rate' => 0.25,
        'attendance_rate' => 0.25,
        'activity_level' => 0.15,
    ],
];
