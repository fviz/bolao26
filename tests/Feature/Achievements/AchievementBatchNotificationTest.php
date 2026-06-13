<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Notifications\AchievementEarned;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('multiple achievements earned at once send one combined notification', function () {
    $user = User::factory()->create(['total_points' => 0]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    $notifications = $user->fresh()->notifications()
        ->where('data->type', 'achievement_earned')
        ->get();

    expect($notifications)->toHaveCount(1);

    $body = $notifications->first()->data['body'] ?? '';

    expect($body)
        ->toContain('Você ganhou a medalha')
        ->toContain('e mais');

    expect($notifications->first()->data['url'])
        ->toBe(route('users.achievements.index', $user, false));
});

test('single achievement uses singular notification copy', function () {
    $user = User::factory()->create();
    $game = Game::factory()->knockout()->create([
        'scheduled_at' => now()->addDay(),
        'local_scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($user)
        ->put(route('games.prediction.upsert', $game), [
            'home_score' => 1,
            'away_score' => 0,
        ])
        ->assertRedirect();

    $notification = $user->fresh()->notifications()
        ->where('data->type', 'achievement_earned')
        ->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['body'])->toBe('Você ganhou a medalha Primeiro Chute 👟.');
});

test('batched achievement notification is queued once', function () {
    Notification::fake();

    $user = User::factory()->create(['total_points' => 0]);
    $game = Game::factory()->finished(['home_score' => 2, 'away_score' => 1])->create();

    Prediction::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    app(ScoreGamePredictions::class)->score($game->fresh());

    Notification::assertSentToTimes($user, AchievementEarned::class, 1);

    Notification::assertSentTo($user, AchievementEarned::class, function (AchievementEarned $notification) {
        return count($notification->achievements) > 1;
    });
});
