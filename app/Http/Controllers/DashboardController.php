<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $games = Game::query()
            ->upcoming()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'games' => GameResource::collection($games),
        ]);
    }
}
