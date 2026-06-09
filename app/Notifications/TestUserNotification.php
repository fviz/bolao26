<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class TestUserNotification extends Notification
{
    use ChoosesNotificationChannels, Queueable;

    public function __construct(
        public string $body,
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
            ->body($this->body)
            ->icon('/logo.png')
            ->badge('/favicon.svg')
            ->action('Ver notificações', 'open_notifications')
            ->data(['url' => route('notifications.index', absolute: false)])
            ->options(['TTL' => 3600]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'type' => 'test_notification',
            'title' => $this->title(),
            'body' => $this->body,
            'url' => route('notifications.index', absolute: false),
        ];
    }

    private function title(): string
    {
        return 'Notificação de teste';
    }
}
