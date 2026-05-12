<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web migration token (optional)
    |--------------------------------------------------------------------------
    |
    | When set, enables POST /_serviceflow/migrate with header X-Migrate-Token
    | for environments without SSH (e.g. some cPanel accounts). Leave empty to
    | disable the route entirely. Remove or clear the token after upgrading.
    |
    */
    'migrate_web_token' => env('MIGRATE_WEB_TOKEN', ''),

];
