<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\MissingPredictionReminder;
use App\Support\NotificationDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notifications:send-game-reminders')]
#[Description('Send reminders for games that are starting soon without a user prediction.')]
class SendGameReminderNotifications extends Command
{
    public function handle(NotificationDispatcher $dispatcher): int
    {
        $sent = 0;

        User::query()
            ->with('notificationPreference')
            ->each(function (User $user) use ($dispatcher, &$sent): void {
                $preference = $user->notificationPreference()->firstOrCreate();

                if (! $preference->missing_prediction_reminders_enabled) {
                    return;
                }

                $minutes = $preference->game_reminder_minutes;

                if (! in_array($minutes, NotificationPreference::GAME_REMINDER_MINUTES, true)) {
                    return;
                }

                $windowStartsAt = now()->addMinutes($minutes);
                $windowEndsAt = $windowStartsAt->copy()->addMinutes(5);

                Game::query()
                    ->whereBetween('scheduled_at', [$windowStartsAt, $windowEndsAt])
                    ->whereDoesntHave('predictions', fn ($query) => $query->where('user_id', $user->id))
                    ->each(function (Game $game) use ($dispatcher, $minutes, $user, &$sent): void {
                        $wasSent = $dispatcher->sendOnce(
                            $user,
                            'missing_prediction_reminder',
                            "missing-prediction:{$user->id}:{$game->id}:{$minutes}",
                            new MissingPredictionReminder($game, $minutes),
                        );

                        if ($wasSent) {
                            $sent++;
                        }
                    });
            });

        $this->components->info("Sent {$sent} game reminder notifications.");

        return self::SUCCESS;
    }
}
