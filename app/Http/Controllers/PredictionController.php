<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Support\ChampionPredictions;
use App\Support\TopScorerPredictions;
use App\Support\WorldCupPlayers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PredictionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $loadUserPredictions = [
            'predictions' => fn ($query) => $query->where('user_id', $user->id),
        ];

        $predictedGames = Game::query()
            ->whereHas('predictions', fn ($query) => $query->where('user_id', $user->id))
            ->with($loadUserPredictions)
            ->withCount('comments')
            ->orderBy('scheduled_at')
            ->paginate(20, ['*'], 'predicted_page')
            ->withQueryString();

        $missingGames = Game::query()
            ->bettingOpen()
            ->whereDoesntHave('predictions', fn ($query) => $query->where('user_id', $user->id))
            ->with($loadUserPredictions)
            ->withCount('comments')
            ->orderBy('scheduled_at')
            ->paginate(20, ['*'], 'missing_page')
            ->withQueryString();

        $championPrediction = $user->championPrediction;
        $topScorerPrediction = $user->topScorerPrediction;

        return Inertia::render('predictions/Index', [
            'predictedGames' => GameResource::collection($predictedGames),
            'missingGames' => GameResource::collection($missingGames),
            'championPrediction' => $championPrediction ? [
                'fifaTeamId' => $championPrediction->fifa_team_id,
                'points' => $championPrediction->points,
            ] : null,
            'championPredictionsOpen' => ChampionPredictions::isOpen(),
            'championPredictionsDeadline' => ChampionPredictions::deadline()->toIso8601String(),
            'championTeams' => ChampionPredictions::availableTeams(),
            'topScorerPrediction' => $topScorerPrediction ? [
                'playerId' => $topScorerPrediction->player_id,
                'points' => $topScorerPrediction->points,
            ] : null,
            'topScorerPredictionsOpen' => TopScorerPredictions::isOpen(),
            'topScorerPredictionsDeadline' => TopScorerPredictions::deadline()->toIso8601String(),
            'players' => WorldCupPlayers::forFrontend(),
            'playerCountrySearchTerms' => WorldCupPlayers::countrySearchTerms(),
        ]);
    }

    public function upsert(StorePredictionRequest $request, Game $game): RedirectResponse
    {
        $request->user()->predictions()->updateOrCreate(
            ['game_id' => $game->id],
            $request->validated(),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Previsão salva.'),
        ]);

        return to_route('games.show', $game);
    }
}
