<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'achievement_id',
    'current_value',
])]
class UserAchievementProgress extends Model
{
    protected $table = 'user_achievement_progress';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
