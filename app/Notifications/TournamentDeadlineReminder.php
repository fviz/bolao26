<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class TournamentDeadlineReminder extends Notification implements ShouldQueue
{
    use ChoosesNotificationChannels, Queueable;

    public function __construct(
        public bool $missingChampionPrediction,
        public bool $missingTopScorerPrediction,
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
            ->action('Fazer previsões', 'open_predictions')
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
            'type' => 'tournament_deadline_reminder',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'missing_champion_prediction' => $this->missingChampionPrediction,
            'missing_top_scorer_prediction' => $this->missingTopScorerPrediction,
        ];
    }

    private function title(): string
    {
        return 'Previsões da Copa pendentes';
    }

    private function body(): string
    {
        return 'Falta pouco tempo para o começo da Copa e você ainda não fez previsões para país campeão ou artilheiro. Faça suas previsões antes do início do campeonato!';
    }

    private function url(): string
    {
        return route('predictions.index', absolute: false);
    }
}
