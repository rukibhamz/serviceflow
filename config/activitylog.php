<?php

return [

    /*
     * Activity logging is enabled.
     */
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    'delete_records_older_than_days' => 365,

    /*
     * Default log name used when no log name is passed to activity().
     */
    'default_log_name' => 'default',

    'default_auth_driver' => null,

    'subject_returns_soft_deleted_models' => false,

    'activity_model' => \Spatie\Activitylog\Models\Activity::class,

    'table_name' => env('ACTIVITY_LOGGER_TABLE_NAME', 'activity_log'),

    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),
];
