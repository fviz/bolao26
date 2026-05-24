<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FIFA API
    |--------------------------------------------------------------------------
    |
    | Configuration for the FIFA calendar API used to sync World Cup matches.
    |
    */

    'base_url' => env('FIFA_API_BASE_URL', 'https://api.fifa.com/api/v3'),

    'season_id' => env('FIFA_SEASON_ID', '285023'),

    'language' => env('FIFA_API_LANGUAGE', 'en'),

    'match_count' => (int) env('FIFA_API_MATCH_COUNT', 500),

    'timeout' => (int) env('FIFA_API_TIMEOUT', 15),

    'connect_timeout' => (int) env('FIFA_API_CONNECT_TIMEOUT', 5),

    'sync_interval_hours' => (int) env('FIFA_SYNC_INTERVAL_HOURS', 6),

    'results_sync_minutes' => (int) env('FIFA_RESULTS_SYNC_MINUTES', 15),

];
