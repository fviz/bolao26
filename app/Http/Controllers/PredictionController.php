<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PredictionController extends Controller
{
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
