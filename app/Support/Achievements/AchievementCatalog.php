<?php

namespace App\Support\Achievements;

use App\Models\Achievement;
use Illuminate\Support\Collection;

class AchievementCatalog
{
    /** @var Collection<string, Achievement>|null */
    private static ?Collection $bySlug = null;

    public static function find(string $slug): ?Achievement
    {
        return self::allBySlug()->get($slug);
    }

    public static function id(string $slug): ?int
    {
        return self::find($slug)?->id;
    }

    /**
     * @return Collection<string, Achievement>
     */
    public static function allBySlug(): Collection
    {
        if (self::$bySlug === null) {
            self::$bySlug = Achievement::query()
                ->orderBy('sort_order')
                ->get()
                ->keyBy('slug');
        }

        return self::$bySlug;
    }

    public static function flush(): void
    {
        self::$bySlug = null;
    }
}
