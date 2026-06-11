<?php

use App\Models\Game;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $scoreGamePredictions = app(ScoreGamePredictions::class);

        Game::query()
            ->where('is_final', true)
            ->where('match_status', '!=', 4)
            ->whereNotNull('scored_at')
            ->each(function (Game $game) use ($scoreGamePredictions): void {
                $scoreGamePredictions->unscore($game);

                $game->update(['is_final' => false]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
