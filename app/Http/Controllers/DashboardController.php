<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Support\ChampionPredictions;
use App\Support\Leaderboard;
use App\Support\TopScorerPredictions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $games = Game::query()
            ->upcoming()
            ->with([
                'predictions' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->withCount('comments')
            ->paginate(10)
            ->withQueryString();

        $entries = Leaderboard::rankedEntries($user);
        $leaderboard = Leaderboard::windowForUser(
            $entries,
            $user->id,
            config('bolao.leaderboard_widget_size', 5),
        );

        $nextGame = Game::query()
            ->upcoming()
            ->with([
                'predictions' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->withCount('comments')
            ->first();

        $featured = Game::featuredForDashboard();

        return Inertia::render('Dashboard', [
            'games' => GameResource::collection($games),
            'userTotalPoints' => $user->total_points,
            'leaderboard' => $leaderboard->values()->all(),
            'nextGame' => $nextGame ? GameResource::make($nextGame) : null,
            'featuredGame' => $featured ? [
                'status' => $featured['status'],
                'game' => GameResource::make($featured['game']),
            ] : null,
            'browserPushAvailable' => filled(config('webpush.vapid.public_key'))
                && filled(config('webpush.vapid.private_key')),
            'championPredictionsOpen' => ChampionPredictions::isOpen(),
            'topScorerPredictionsOpen' => TopScorerPredictions::isOpen(),
            'hasChampionPrediction' => $user->championPrediction !== null,
            'hasTopScorerPrediction' => $user->topScorerPrediction !== null,
        ]);
    }
}
