<?php

use App\Models\Game;
use App\Models\GameComment;
use App\Models\Prediction;
use App\Models\User;
use App\Models\UserAchievementProgress;
use App\Services\Achievements\AchievementBackfiller;
use App\Services\Scoring\ScoreGamePredictions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function progressFor(User $user, string $slug): int
{
    return UserAchievementProgress::query()
        ->where('user_id', $user->id)
        ->whereHas('achievement', fn ($query) => $query->where('slug', $slug))
        ->value('current_value') ?? 0;
}

test('logging in between midnight and 6am awards o bacurau', function () {
    $user = User::factory()->create();

    $this->travelTo(now()->timezone('America/Sao_Paulo')->setTime(3, 0)->timezone('UTC'));

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(userHasAchievement($user->fresh(), 'o-bacurau'))->toBeTrue();
});

test('logging in outside the night window does not award o bacurau', function () {
    $user = User::factory()->create();

    $this->travelTo(now()->timezone('America/Sao_Paulo')->setTime(12, 0)->timezone('UTC'));

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(userHasAchievement($user->fresh(), 'o-bacurau'))->toBeFalse();
});

test('fifth comment awards falador with full progress', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    GameComment::factory()->for($user)->for($game)->count(4)->create();

    expect(progressFor($user, 'falador'))->toBe(0)
        ->and(userHasAchievement($user, 'falador'))->toBeFalse();

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), ['body' => 'Quinto comentário!']);

    expect(userHasAchievement($user->fresh(), 'falador'))->toBeTrue()
        ->and(progressFor($user, 'falador'))->toBe(5);
});

test('uploading a profile photo awards boa pinta', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    expect(userHasAchievement($user, 'boa-pinta'))->toBeFalse();

    $this->actingAs($user)
        ->post(route('profile.avatar.store'), [
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    expect(userHasAchievement($user->fresh(), 'boa-pinta'))->toBeTrue();
});

test('fifth zero point game awards azarado with full progress', function () {
    $user = User::factory()->create();

    collect(range(1, 5))->each(function (int $index) use ($user) {
        $game = Game::factory()->finished([
            'home_score' => 2,
            'away_score' => 0,
            'scheduled_at' => now()->subDays(6 - $index),
            'local_scheduled_at' => now()->subDays(6 - $index),
        ])->create();

        Prediction::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'home_score' => 0,
            'away_score' => 2,
        ]);

        app(ScoreGamePredictions::class)->score($game->fresh());
    });

    expect(userHasAchievement($user->fresh(), 'azarado'))->toBeTrue()
        ->and(progressFor($user, 'azarado'))->toBe(5);
});

test('backfill awards falador and boa pinta from existing history', function () {
    Storage::fake('public');

    $user = User::factory()->create(['avatar_path' => 'avatars/1/photo.jpg']);
    $game = Game::factory()->create();

    GameComment::factory()->for($user)->for($game)->count(5)->create();

    app(AchievementBackfiller::class)->backfill($user);

    expect(userHasAchievement($user->fresh(), 'falador'))->toBeTrue()
        ->and(userHasAchievement($user->fresh(), 'boa-pinta'))->toBeTrue();
});

test('backfill awards azarado for pre existing zero point games', function () {
    $user = User::factory()->create();

    collect(range(1, 5))->each(function (int $index) use ($user) {
        $scoredAt = now()->subDays(6 - $index);

        $game = Game::factory()->finished([
            'home_score' => 2,
            'away_score' => 0,
            'scheduled_at' => $scoredAt,
            'local_scheduled_at' => $scoredAt,
            'scored_at' => $scoredAt,
        ])->create();

        Prediction::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'home_score' => 0,
            'away_score' => 2,
            'points' => 0,
            'scored_at' => $scoredAt,
        ]);
    });

    app(AchievementBackfiller::class)->backfill($user);

    expect(userHasAchievement($user->fresh(), 'azarado'))->toBeTrue();
});
