<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TournamentDeadlineReminder;
use App\Support\NotificationDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notifications:send-tournament-deadline')]
#[Description('Send one-time reminders for missing champion or top scorer predictions.')]
class SendTournamentDeadlineNotifications extends Command
{
    public function handle(NotificationDispatcher $dispatcher): int
    {
        $deadline = $this->deadline();

        if ($deadline === null || now()->lt($deadline->subDay()) || now()->gt($deadline)) {
            $this->components->info('Tournament prediction deadline is not inside the reminder window.');

            return self::SUCCESS;
        }

        $sent = 0;

        User::query()
            ->with(['championPrediction', 'notificationPreference', 'topScorerPrediction'])
            ->each(function (User $user) use ($deadline, $dispatcher, &$sent): void {
                $preference = $user->notificationPreference()->firstOrCreate();

                if (! $preference->tournament_deadline_enabled) {
                    return;
                }

                $missingChampionPrediction = $user->championPrediction === null;
                $missingTopScorerPrediction = $user->topScorerPrediction === null;

                if (! $missingChampionPrediction && ! $missingTopScorerPrediction) {
                    return;
                }

                $wasSent = $dispatcher->sendOnce(
                    $user,
                    'tournament_deadline_reminder',
                    "tournament-deadline:{$user->id}:{$deadline->toIso8601String()}",
                    new TournamentDeadlineReminder($missingChampionPrediction, $missingTopScorerPrediction),
                );

                if ($wasSent) {
                    $sent++;
                }
            });

        $this->components->info("Sent {$sent} tournament deadline notifications.");

        return self::SUCCESS;
    }

    private function deadline(): ?CarbonImmutable
    {
        $deadlines = collect([
            config('bolao.champion_predictions_deadline'),
            config('bolao.top_scorer_predictions_deadline'),
        ])
            ->filter()
            ->map(fn (string $deadline): CarbonImmutable => CarbonImmutable::parse($deadline));

        if ($deadlines->isEmpty()) {
            return null;
        }

        return $deadlines->min();
    }
}
