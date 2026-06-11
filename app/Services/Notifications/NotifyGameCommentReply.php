<?php

namespace App\Services\Notifications;

use App\Models\GameComment;
use App\Notifications\GameCommentReplyReceived;
use App\Support\NotificationDispatcher;

class NotifyGameCommentReply
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function notify(GameComment $reply): void
    {
        if ($reply->parent_id === null) {
            return;
        }

        $reply->loadMissing(['user', 'game', 'parent.user']);
        $parentAuthor = $reply->parent?->user;

        if ($parentAuthor === null || $parentAuthor->id === $reply->user_id) {
            return;
        }

        if (! $parentAuthor->notificationPreference()->firstOrCreate()->comment_reply_notifications_enabled) {
            return;
        }

        $this->notifications->sendOnce(
            $parentAuthor,
            'game_comment_reply_received',
            "game-comment-reply:{$parentAuthor->id}:{$reply->id}",
            new GameCommentReplyReceived($reply),
        );
    }
}
