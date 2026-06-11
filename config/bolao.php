<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Champion predictions deadline
    |--------------------------------------------------------------------------
    |
    | Users may pick the tournament champion until this moment (UTC).
    |
    */

    'champion_predictions_deadline' => env(
        'CHAMPION_PREDICTIONS_DEADLINE',
        '2026-06-11 19:00:00',
    ),

    /*
    |--------------------------------------------------------------------------
    | Top scorer predictions deadline
    |--------------------------------------------------------------------------
    |
    | Users may pick the tournament top scorer until this moment (UTC).
    |
    */

    'top_scorer_predictions_deadline' => env(
        'TOP_SCORER_PREDICTIONS_DEADLINE',
        env('CHAMPION_PREDICTIONS_DEADLINE', '2026-06-11 19:00:00'),
    ),

    /*
    |--------------------------------------------------------------------------
    | Final stage names
    |--------------------------------------------------------------------------
    |
    | Stage names (from FIFA sync) that identify the tournament final match.
    |
    */

    'final_stage_names' => [
        'Final',
    ],

    /*
    |--------------------------------------------------------------------------
    | Leaderboard widget size
    |--------------------------------------------------------------------------
    |
    | Number of ranking positions shown on the dashboard widget.
    |
    */

    'leaderboard_widget_size' => (int) env('BOLAO_LEADERBOARD_WIDGET_SIZE', 5),

    /*
    |--------------------------------------------------------------------------
    | Likely live window
    |--------------------------------------------------------------------------
    |
    | Minutes after kickoff during which a non-final game is treated as
    | in progress on the dashboard (no FIFA API calls; schedule-based only).
    |
    */

    'likely_live_window_minutes' => (int) env('BOLAO_LIKELY_LIVE_WINDOW_MINUTES', 150),

];
