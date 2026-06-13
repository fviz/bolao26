<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\Leaderboard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = $request->user();
        $entries = Leaderboard::rankedEntries($currentUser);
        $entry = $entries->firstWhere('id', $this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'totalPoints' => $this->total_points,
            'rank' => $entry['rank'] ?? 1,
            'isCurrentUser' => $currentUser !== null && $currentUser->is($this->resource),
        ];
    }
}
