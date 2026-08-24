<?php

return [
    /*
     * Hour of day (24h, server time) after which a volunteer who hasn't
     * submitted today's daily report is considered "missed" and reminded.
     */
    'report_cutoff_hour' => env('REMINDER_REPORT_CUTOFF_HOUR', 18),
];
