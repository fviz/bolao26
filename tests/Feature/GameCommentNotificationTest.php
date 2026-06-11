<?php

use App\Models\Game;
use App\Models\GameComment;
use App\Models\User;
use App\Notifications\GameCommentReplyReceived;
use App\Services\Notifications\NotifyGameCommentReply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('replying to another users comment notifies the parent author', function () {
    Notification::fake();

    $parentAuthor = User::factory()->create(['name' => 'Maria']);
    $replier = User::factory()->create(['name' => 'João']);
    $game = Game::factory()->create([
        'home_name' => 'Brazil',
        'away_name' => 'France',
    ]);
    $parent = GameComment::factory()->for($game)->for($parentAuthor)->create();

    $this->actingAs($replier)
        ->post(route('games.comments.store', $game), [
            'body' => 'Concordo!',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect(route('games.show', $game));

    Notification::assertSentTo($parentAuthor, GameCommentReplyReceived::class, function (GameCommentReplyReceived $notification) use ($parentAuthor, $replier, $game): bool {
        $payload = $notification->toArray($parentAuthor);

        return $payload['type'] === 'game_comment_reply_received'
            && $payload['title'] === 'Nova resposta no comentário'
            && $payload['body'] === "{$replier->name} respondeu seu comentário na partida {$game->matchTitle()}"
            && $payload['url'] === route('games.show', $game, false)
            && $payload['game_id'] === $game->id;
    });

    Notification::assertNotSentTo($replier, GameCommentReplyReceived::class);
});

test('top level comments do not send reply notifications', function () {
    Notification::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $game = Game::factory()->create();
    GameComment::factory()->for($game)->for($otherUser)->create();

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), [
            'body' => 'Primeiro comentário',
        ])
        ->assertRedirect(route('games.show', $game));

    Notification::assertNothingSent();
});

test('reply notifications are skipped when parent author disabled them', function () {
    Notification::fake();

    $parentAuthor = User::factory()->create();
    $replier = User::factory()->create();
    $game = Game::factory()->create();
    $parent = GameComment::factory()->for($game)->for($parentAuthor)->create();

    $parentAuthor->notificationPreference()->create([
        'comment_reply_notifications_enabled' => false,
    ]);

    $this->actingAs($replier)
        ->post(route('games.comments.store', $game), [
            'body' => 'Concordo!',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect(route('games.show', $game));

    Notification::assertNothingSent();
});

test('reply notifications are deduplicated per recipient and reply', function () {
    Notification::fake();

    $parentAuthor = User::factory()->create();
    $replier = User::factory()->create();
    $game = Game::factory()->create();
    $parent = GameComment::factory()->for($game)->for($parentAuthor)->create();

    $reply = GameComment::factory()->for($game)->for($replier)->replyTo($parent)->create();

    app(NotifyGameCommentReply::class)->notify($reply);
    app(NotifyGameCommentReply::class)->notify($reply);

    Notification::assertSentToTimes($parentAuthor, GameCommentReplyReceived::class, 1);
});
