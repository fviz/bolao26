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
    public function show(Request $request, Game $game): Response
    {
        $game->load([
            'predictions.user',
            'topLevelComments.user',
            'topLevelComments.replies.user',
        ]);
        $game->loadCount('comments');

        return Inertia::render('games/Show', [
            'game' => GameResource::make($game),
            'comments' => GameCommentResource::collection($game->topLevelComments),
        ]);
    }
}
