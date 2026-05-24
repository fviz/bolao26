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
        '2026-06-11 00:00:00',
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

];
