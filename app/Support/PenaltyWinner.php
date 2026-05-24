<?php

namespace App\Support;

final class PenaltyWinner
{
    public const string Home = 'home';

    public const string Away = 'away';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::Home, self::Away];
    }
}
