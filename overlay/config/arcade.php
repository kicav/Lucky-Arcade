<?php

return [
    'operations' => [
        'notification_retention_days' => (int) env('ARCADE_NOTIFICATION_RETENTION_DAYS', 90),
        'security_event_retention_days' => (int) env('ARCADE_SECURITY_RETENTION_DAYS', 180),
        'operation_run_retention_days' => (int) env('ARCADE_OPERATION_RETENTION_DAYS', 90),
        'backup_keep' => (int) env('ARCADE_BACKUP_KEEP', 14),
        'queue_warning_threshold' => (int) env('ARCADE_QUEUE_WARNING_THRESHOLD', 100),
    ],
];
