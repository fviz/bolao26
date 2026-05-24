<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'fifa_match_id',
    'match_number',
    'id_season',
    'id_stage',
    'id_group',
    'stage_name',
    'group_name',
    'scheduled_at',
    'local_scheduled_at',
    'home_fifa_team_id',
    'home_name',
    'home_abbr',
    'home_placeholder',
    'away_fifa_team_id',
    'away_name',
    'away_abbr',
    'away_placeholder',
    'stadium_name',
    'city_name',
    'match_status',
    'home_score',
    'away_score',
    'time_defined',
    'is_final',
    'payload',
    'synced_at',
])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'local_scheduled_at' => 'datetime',
            'match_status' => 'integer',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'time_defined' => 'boolean',
            'is_final' => 'boolean',
            'payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function userPrediction(User $user): ?Prediction
    {
        return $this->predictions()
            ->where('user_id', $user->id)
            ->first();
    }

    public function bettingClosesAt(): CarbonInterface
    {
        return $this->scheduled_at->copy()->subMinute();
    }

    public function isBettingOpen(): bool
    {
        return now()->lt($this->bettingClosesAt());
    }

    public function homeDisplayName(): string
    {
        return $this->home_name ?? $this->home_placeholder ?? '—';
    }

    public function awayDisplayName(): string
    {
        return $this->away_name ?? $this->away_placeholder ?? '—';
    }

    public function matchTitle(): string
    {
        return "{$this->homeDisplayName()} x {$this->awayDisplayName()}";
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at');
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeNotFinal(Builder $query): Builder
    {
        return $query->where('is_final', false);
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeKickoffPassed(Builder $query): Builder
    {
        return $query->where('scheduled_at', '<=', now());
    }
}
