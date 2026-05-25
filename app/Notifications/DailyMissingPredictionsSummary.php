<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class DailyMissingPredictionsSummary extends Notification implements ShouldQueue
{
    use ChoosesNotificationChannels, Queueable;

    public function __construct(
        public int $gamesToday,
        public int $missingPredictions,
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
            ->action('Ver previsões', 'open_predictions')
            ->data(['url' => $this->url()])
            ->options(['TTL' => 43200]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'type' => 'daily_missing_predictions_summary',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'games_today' => $this->gamesToday,
            'missing_predictions' => $this->missingPredictions,
        ];
    }

    private function title(): string
    {
        return 'Previsões de hoje';
    }

    private function body(): string
    {
        return "Hoje tem {$this->gamesToday} jogos e você ainda tem que fazer {$this->missingPredictions} predições.";
    }

    private function url(): string
    {
        return route('predictions.index', absolute: false);
    }
}
