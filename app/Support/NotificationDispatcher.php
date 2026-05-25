<?php

namespace App\Support;

use App\Models\NotificationDispatch;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationDispatcher
{
    public function sendOnce(User $user, string $type, string $dedupeKey, Notification $notification): bool
    {
        $dispatch = NotificationDispatch::query()->firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'user_id' => $user->id,
                'type' => $type,
                'sent_at' => now(),
            ],
        );

        if (! $dispatch->wasRecentlyCreated) {
            return false;
        }

        $user->notify($notification);

        return true;
    }
}
