<?php

namespace App\Http\Controllers;

use App\Support\Leaderboard;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RankingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('ranking/Index', [
            'leaderboard' => Leaderboard::rankedEntries($user)->values()->all(),
            'medalLeaderboard' => Leaderboard::medalRankedEntries($user)->values()->all(),
        ]);
    }
}
