<?php

namespace App\Notifications;

use App\Models\Game;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class GameFinishedPredictionScored extends Notification implements ShouldQueue
{
    use ChoosesNotificationChannels, Queueable;

    public function __construct(
        public Game $game,
        public int $points,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
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
            ->action('Ver resultado', 'open_game')
            ->data(['url' => $this->url()])
            ->options(['TTL' => 86400]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'type' => 'game_finished_prediction_scored',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'game_id' => $this->game->id,
            'points' => $this->points,
        ];
    }

    private function title(): string
    {
        return 'Resultado do jogo';
    }

    private function body(): string
    {
        return "{$this->game->matchTitle()} terminou! Sua previsão garantiu {$this->points} pontos.";
    }

    private function url(): string
    {
        return route('games.show', $this->game, false);
    }
}
