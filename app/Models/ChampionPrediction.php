<?php

namespace App\Models;

use Database\Factories\ChampionPredictionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'fifa_team_id',
    'points',
    'scored_at',
])]
class ChampionPrediction extends Model
{
    /** @use HasFactory<ChampionPredictionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'scored_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
