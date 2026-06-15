<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameCommentResource;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $games = Game::query()
            ->with([
                'predictions' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->withCount('comments')
            ->orderBy('scheduled_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('games/Index', [
            'games' => Inertia::scroll(
                GameResource::collection($games),
            ),
        ]);
    }

    public function show(Request $request, Game $game): Response
    {
        $game->load([
            'predictions.user.featuredAchievement',
            'topLevelComments.user.featuredAchievement',
            'topLevelComments.replies.user.featuredAchievement',
        ]);
        $game->loadCount('comments');

        return Inertia::render('games/Show', [
            'game' => GameResource::make($game),
            'comments' => GameCommentResource::collection($game->topLevelComments),
        ]);
    }
}
