<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyTopScorerPredictionRequest;
use App\Http\Requests\StoreTopScorerPredictionRequest;
use Illuminate\Http\RedirectResponse;

class TopScorerPredictionController extends Controller
{
    public function upsert(StoreTopScorerPredictionRequest $request): RedirectResponse
    {
        $request->user()->topScorerPrediction()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        return to_route('predictions.index');
    }

    public function destroy(DestroyTopScorerPredictionRequest $request): RedirectResponse
    {
        $request->user()->topScorerPrediction?->delete();

        return to_route('predictions.index');
    }
}
