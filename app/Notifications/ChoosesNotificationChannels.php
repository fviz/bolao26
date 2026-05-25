<?php

namespace App\Notifications;

use App\Models\User;
use NotificationChannels\WebPush\WebPushChannel;

trait ChoosesNotificationChannels
{
    /**
     * @return list<string>
     */
    protected function channelsFor(User $notifiable): array
    {
        $preference = $notifiable->notificationPreference()->firstOrCreate();
        $channels = ['database'];

        if (
            $preference->browser_notifications_enabled
            && filled(config('webpush.vapid.public_key'))
            && filled(config('webpush.vapid.private_key'))
            && $notifiable->pushSubscriptions()->exists()
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }
}
