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
    'home_penalty_score',
    'away_penalty_score',
    'penalty_winner',
    'time_defined',
    'is_final',
    'payload',
    'synced_at',
    'scored_at',
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
            'home_penalty_score' => 'integer',
            'away_penalty_score' => 'integer',
            'time_defined' => 'boolean',
            'is_final' => 'boolean',
            'payload' => 'array',
            'synced_at' => 'datetime',
            'scored_at' => 'datetime',
        ];
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(GameComment::class);
    }

    public function topLevelComments(): HasMany
    {
        return $this->hasMany(GameComment::class)->whereNull('parent_id')->orderBy('created_at');
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
        if ($this->scheduled_at === null || ! $this->scheduled_at->isFuture()) {
            return false;
        }

        return now()->lt($this->bettingClosesAt());
    }

    public function arePredictionsVisible(): bool
    {
        return ! $this->isBettingOpen();
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
    public function scopeBettingOpen(Builder $query): Builder
    {
        return $query->where('scheduled_at', '>', now()->addMinute());
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

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeResultsSyncCandidates(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->kickoffPassed()
                ->orWhere('is_final', true);
        });
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('is_final', true);
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeLikelyLive(Builder $query): Builder
    {
        $windowMinutes = config('bolao.likely_live_window_minutes', 150);

        return $query
            ->where('is_final', false)
            ->where('scheduled_at', '<=', now())
            ->where('scheduled_at', '>', now()->subMinutes($windowMinutes));
    }

    /**
     * @return array{game: self, status: 'live'|'finished'}|null
     */
    public static function featuredForDashboard(): ?array
    {
        $live = static::query()
            ->likelyLive()
            ->orderByDesc('scheduled_at')
            ->first();

        if ($live !== null) {
            return ['game' => $live, 'status' => 'live'];
        }

        $finished = static::query()
            ->finished()
            ->orderByDesc('scheduled_at')
            ->first();

        if ($finished !== null) {
            return ['game' => $finished, 'status' => 'finished'];
        }

        return null;
    }

    public function isGroupStage(): bool
    {
        return $this->id_group !== null;
    }

    public function isKnockout(): bool
    {
        return ! $this->isGroupStage();
    }

    public function wentToPenalties(): bool
    {
        return $this->home_penalty_score !== null
            && $this->away_penalty_score !== null
            && ($this->home_penalty_score > 0 || $this->away_penalty_score > 0);
    }

    public function penaltyWinnerSide(): ?string
    {
        return $this->penalty_winner;
    }

    public function isTournamentFinal(): bool
    {
        return in_array($this->stage_name, config('bolao.final_stage_names', []), true);
    }

    public function winningFifaTeamId(): ?string
    {
        $side = $this->matchWinnerSide();

        if ($side === 'home') {
            return $this->home_fifa_team_id;
        }

        if ($side === 'away') {
            return $this->away_fifa_team_id;
        }

        return null;
    }

    public function matchWinnerSide(): ?string
    {
        if ($this->wentToPenalties() && $this->penalty_winner !== null) {
            return $this->penalty_winner;
        }

        if ($this->home_score === null || $this->away_score === null) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return 'home';
        }

        if ($this->away_score > $this->home_score) {
            return 'away';
        }

        return null;
    }

    public function isReadyForScoring(): bool
    {
        return $this->is_final
            && $this->home_score !== null
            && $this->away_score !== null;
    }
}
