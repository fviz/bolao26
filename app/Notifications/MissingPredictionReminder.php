<?php

namespace App\Notifications;

use App\Models\Game;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class MissingPredictionReminder extends Notification implements ShouldQueue
{
    use ChoosesNotificationChannels, Queueable;

    public function __construct(
        public Game $game,
        public int $minutesBeforeKickoff,
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
            ->action('Fazer previsão', 'open_prediction')
            ->data(['url' => $this->url()])
            ->options(['TTL' => 3600]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'type' => 'missing_prediction_reminder',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'game_id' => $this->game->id,
            'minutes_before_kickoff' => $this->minutesBeforeKickoff,
        ];
    }

    private function title(): string
    {
        return 'Previsão pendente';
    }

    private function body(): string
    {
        return "{$this->game->matchTitle()} começa em {$this->leadTimeLabel()} e você ainda não fez uma previsão! Corra e faça sua previsão antes que o jogo comece.";
    }

    private function leadTimeLabel(): string
    {
        return match ($this->minutesBeforeKickoff) {
            30 => '30 minutos',
            60 => 'uma hora',
            180 => '3 horas',
            360 => '6 horas',
            720 => '12 horas',
            1440 => '24 horas',
            default => "{$this->minutesBeforeKickoff} minutos",
        };
    }

    private function url(): string
    {
        return route('games.show', $this->game, false);
    }
}
