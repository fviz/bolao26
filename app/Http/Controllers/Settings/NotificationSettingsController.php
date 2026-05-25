<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\NotificationSettingsUpdateRequest;
use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $preference = $request->user()
            ->notificationPreference()
            ->firstOrCreate();

        return Inertia::render('settings/Notifications', [
            'preferences' => [
                'missingPredictionRemindersEnabled' => $preference->missing_prediction_reminders_enabled,
                'gameResultNotificationsEnabled' => $preference->game_result_notifications_enabled,
                'dailySummaryEnabled' => $preference->daily_summary_enabled,
                'tournamentDeadlineEnabled' => $preference->tournament_deadline_enabled,
                'browserNotificationsEnabled' => $preference->browser_notifications_enabled,
                'gameReminderMinutes' => $preference->game_reminder_minutes,
                'dailySummaryTime' => substr((string) $preference->daily_summary_time, 0, 5),
                'dailySummaryTimezone' => $preference->daily_summary_timezone,
            ],
            'gameReminderMinuteOptions' => NotificationPreference::GAME_REMINDER_MINUTES,
            'browserPushPublicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    public function update(NotificationSettingsUpdateRequest $request): RedirectResponse
    {
        $request->user()
            ->notificationPreference()
            ->updateOrCreate([], [
                'missing_prediction_reminders_enabled' => $request->boolean('missing_prediction_reminders_enabled'),
                'game_result_notifications_enabled' => $request->boolean('game_result_notifications_enabled'),
                'daily_summary_enabled' => $request->boolean('daily_summary_enabled'),
                'tournament_deadline_enabled' => $request->boolean('tournament_deadline_enabled'),
                'browser_notifications_enabled' => $request->boolean('browser_notifications_enabled'),
                'game_reminder_minutes' => $request->integer('game_reminder_minutes'),
                'daily_summary_time' => $request->string('daily_summary_time')->toString(),
                'daily_summary_timezone' => $request->string('daily_summary_timezone')->toString(),
            ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Notificações atualizadas.')]);

        return to_route('notifications.settings.edit');
    }
}
