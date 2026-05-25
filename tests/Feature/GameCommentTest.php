<?php

use App\Models\Game;
use App\Models\GameComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot post a comment', function () {
    $game = Game::factory()->create();

    $this->post(route('games.comments.store', $game), [
        'body' => 'Hello',
    ])->assertRedirect(route('login'));
});

test('guests cannot delete a comment', function () {
    $game = Game::factory()->create();
    $comment = GameComment::factory()->for($game)->create();

    $this->delete(route('games.comments.destroy', [$game, $comment]))
        ->assertRedirect(route('login'));
});

test('authenticated user can post a top level comment', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), [
            'body' => 'Boa sorte a todos!',
        ])
        ->assertRedirect(route('games.show', $game));

    $comment = GameComment::query()
        ->where('game_id', $game->id)
        ->where('user_id', $user->id)
        ->first();

    expect($comment)->not->toBeNull()
        ->and($comment->body)->toBe('Boa sorte a todos!')
        ->and($comment->parent_id)->toBeNull();
});

test('comment body is required and max 250 characters', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), [
            'body' => '',
        ])
        ->assertSessionHasErrors('body');

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), [
            'body' => str_repeat('a', 251),
        ])
        ->assertSessionHasErrors('body');
});

test('user can reply to a top level comment', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $parent = GameComment::factory()->for($game)->create([
        'body' => 'Comentário pai',
    ]);

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), [
            'body' => 'Concordo!',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect(route('games.show', $game));

    $reply = GameComment::query()
        ->where('parent_id', $parent->id)
        ->where('user_id', $user->id)
        ->first();

    expect($reply)->not->toBeNull()
        ->and($reply->body)->toBe('Concordo!')
        ->and($reply->game_id)->toBe($game->id);
});

test('user cannot reply to a reply', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $parent = GameComment::factory()->for($game)->create();
    $reply = GameComment::factory()->for($game)->replyTo($parent)->create();

    $this->actingAs($user)
        ->post(route('games.comments.store', $game), [
            'body' => 'Tentativa inválida',
            'parent_id' => $reply->id,
        ])
        ->assertSessionHasErrors('parent_id');
});

test('author can delete own comment', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $comment = GameComment::factory()->for($game)->for($user)->create();

    $this->actingAs($user)
        ->delete(route('games.comments.destroy', [$game, $comment]))
        ->assertRedirect(route('games.show', $game));

    expect(GameComment::query()->find($comment->id))->toBeNull();
});

test('non author cannot delete comment', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    $game = Game::factory()->create();
    $comment = GameComment::factory()->for($game)->for($author)->create();

    $this->actingAs($other)
        ->delete(route('games.comments.destroy', [$game, $comment]))
        ->assertForbidden();

    expect(GameComment::query()->find($comment->id))->not->toBeNull();
});

test('deleting top level comment cascades replies', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $parent = GameComment::factory()->for($game)->for($user)->create();
    $reply = GameComment::factory()->for($game)->replyTo($parent)->create();

    $this->actingAs($user)
        ->delete(route('games.comments.destroy', [$game, $parent]))
        ->assertRedirect(route('games.show', $game));

    expect(GameComment::query()->find($parent->id))->toBeNull()
        ->and(GameComment::query()->find($reply->id))->toBeNull();
});

test('comment must belong to the game in destroy route', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $otherGame = Game::factory()->create();
    $comment = GameComment::factory()->for($otherGame)->for($user)->create();

    $this->actingAs($user)
        ->delete(route('games.comments.destroy', [$game, $comment]))
        ->assertNotFound();
});
