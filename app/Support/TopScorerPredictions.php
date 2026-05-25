<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class TopScorerPredictions
{
    public static function isOpen(): bool
    {
        return now()->lt(self::deadline());
    }

    public static function deadline(): Carbon
    {
        return Carbon::parse(config('bolao.top_scorer_predictions_deadline'));
    }
}
