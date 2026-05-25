<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyGameCommentRequest;
use App\Http\Requests\StoreGameCommentRequest;
use App\Models\Game;
use App\Models\GameComment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class GameCommentController extends Controller
{
    public function store(StoreGameCommentRequest $request, Game $game): RedirectResponse
    {
        $request->user()->gameComments()->create([
            'game_id' => $game->id,
            'body' => $request->validated('body'),
            'parent_id' => $request->validated('parent_id'),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Comentário publicado.'),
        ]);

        return to_route('games.show', $game);
    }

    public function destroy(DestroyGameCommentRequest $request, Game $game, GameComment $comment): RedirectResponse
    {
        $comment->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Comentário excluído.'),
        ]);

        return to_route('games.show', $game);
    }
}
