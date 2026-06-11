<?php

namespace App\Notifications;

use App\Models\GameComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class GameCommentReplyReceived extends Notification implements ShouldQueue
{
    use ChoosesNotificationChannels, Queueable;

    public function __construct(
        public GameComment $reply,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toWebPush(User $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title())
            ->body($this->body())
            ->icon('/logo.png')
            ->badge('/favicon.svg')
            ->action('Ver comentário', 'open_game')
            ->data(['url' => $this->url()])
            ->options(['TTL' => 86400]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'type' => 'game_comment_reply_received',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'game_id' => $this->reply->game_id,
            'comment_id' => $this->reply->id,
            'parent_comment_id' => $this->reply->parent_id,
        ];
    }

    private function title(): string
    {
        return 'Nova resposta no comentário';
    }

    private function body(): string
    {
        $replierName = $this->reply->user->name;
        $matchTitle = $this->reply->game->matchTitle();

        return "{$replierName} respondeu seu comentário na partida {$matchTitle}";
    }

    private function url(): string
    {
        return route('games.show', $this->reply->game, false);
    }
}
