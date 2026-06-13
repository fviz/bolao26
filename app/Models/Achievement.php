<?php

namespace App\Models;

use App\Enums\AchievementTier;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug',
    'name',
    'description',
    'emoji',
    'tier',
    'sort_order',
    'progress_target',
])]
class Achievement extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => AchievementTier::class,
            'sort_order' => 'integer',
            'progress_target' => 'integer',
        ];
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserAchievementProgress::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
