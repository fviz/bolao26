<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChampionPredictionRequest;
use Illuminate\Http\RedirectResponse;

class ChampionPredictionController extends Controller
{
    public function upsert(StoreChampionPredictionRequest $request): RedirectResponse
    {
        $request->user()->championPrediction()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated(),
        );

        return to_route('predictions.index');
    }
}
