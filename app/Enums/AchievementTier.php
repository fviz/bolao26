<?php

namespace App\Enums;

enum AchievementTier: string
{
    case LixoHumano = 'lixo_humano';
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Diamond = 'diamond';

    public function label(): string
    {
        return match ($this) {
            self::LixoHumano => 'Lixo Humano',
            self::Bronze => 'Cobre',
            self::Silver => 'Prata',
            self::Gold => 'Ouro',
            self::Diamond => 'Diamante',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::LixoHumano => 0,
            self::Bronze => 1,
            self::Silver => 2,
            self::Gold => 3,
            self::Diamond => 4,
        };
    }
}
