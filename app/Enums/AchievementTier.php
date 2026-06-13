<?php

namespace App\Enums;

enum AchievementTier: string
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Diamond = 'diamond';

    public function label(): string
    {
        return match ($this) {
            self::Bronze => 'Cobre',
            self::Silver => 'Prata',
            self::Gold => 'Ouro',
            self::Diamond => 'Diamante',
        };
    }
}
