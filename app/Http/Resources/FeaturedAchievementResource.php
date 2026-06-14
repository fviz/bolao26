<?php

namespace App\Http\Resources;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Achievement
 */
class FeaturedAchievementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'emoji' => $this->emoji,
            'tier' => $this->tier->value,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function forUser(User $user): ?array
    {
        if (! $user->relationLoaded('featuredAchievement') || $user->featuredAchievement === null) {
            return null;
        }

        return self::make($user->featuredAchievement)->resolve();
    }
}
