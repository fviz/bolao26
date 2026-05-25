<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TestBrowserNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Teste de notificação')
            ->body('Se você recebeu este aviso, as notificações do navegador estão funcionando.')
            ->icon('/logo.png')
            ->badge('/favicon.svg')
            ->data(['url' => route('notifications.settings.edit', absolute: false)])
            ->options(['TTL' => 60]);
    }
}
