<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\User;
use App\Notifications\DailyMissingPredictionsSummary;
use App\Support\NotificationDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notifications:send-daily-summary')]
#[Description('Send daily summaries for users with missing predictions today.')]
class SendDailyPredictionSummaryNotifications extends Command
{
    public function handle(NotificationDispatcher $dispatcher): int
    {
        $sent = 0;

        User::query()
            ->with('notificationPreference')
            ->each(function (User $user) use ($dispatcher, &$sent): void {
                $preference = $user->notificationPreference()->firstOrCreate();

                if (! $preference->daily_summary_enabled || ! $this->shouldSendForLocalTime($preference->daily_summary_time, $preference->daily_summary_timezone)) {
                    return;
                }

                $nowInTimezone = CarbonImmutable::now($preference->daily_summary_timezone);
                $startsAt = $nowInTimezone->startOfDay()->utc();
                $endsAt = $nowInTimezone->endOfDay()->utc();

                $gamesToday = Game::query()
                    ->whereBetween('scheduled_at', [$startsAt, $endsAt])
                    ->count();

                $missingPredictions = Game::query()
                    ->whereBetween('scheduled_at', [$startsAt, $endsAt])
                    ->bettingOpen()
                    ->whereDoesntHave('predictions', fn ($query) => $query->where('user_id', $user->id))
                    ->count();

                if ($gamesToday === 0 || $missingPredictions === 0) {
                    return;
                }

                $date = $nowInTimezone->toDateString();
                $wasSent = $dispatcher->sendOnce(
                    $user,
                    'daily_missing_predictions_summary',
                    "daily-summary:{$user->id}:{$date}",
                    new DailyMissingPredictionsSummary($gamesToday, $missingPredictions),
                );

                if ($wasSent) {
                    $sent++;
                }
            });

        $this->components->info("Sent {$sent} daily summary notifications.");

        return self::SUCCESS;
    }

    private function shouldSendForLocalTime(string $time, string $timezone): bool
    {
        $now = CarbonImmutable::now($timezone);
        $scheduledAt = $now->setTimeFromTimeString($time);

        return $now->betweenIncluded($scheduledAt, $scheduledAt->addMinutes(5));
    }
}
