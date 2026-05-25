<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'missing_prediction_reminders_enabled',
    'game_result_notifications_enabled',
    'daily_summary_enabled',
    'tournament_deadline_enabled',
    'browser_notifications_enabled',
    'game_reminder_minutes',
    'daily_summary_time',
    'daily_summary_timezone',
])]
class NotificationPreference extends Model
{
    /**
     * @var list<int>
     */
    public const GAME_REMINDER_MINUTES = [30, 60, 180, 360, 720, 1440];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'missing_prediction_reminders_enabled' => true,
        'game_result_notifications_enabled' => true,
        'daily_summary_enabled' => true,
        'tournament_deadline_enabled' => true,
        'browser_notifications_enabled' => false,
        'game_reminder_minutes' => 60,
        'daily_summary_time' => '09:00',
        'daily_summary_timezone' => 'UTC',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'missing_prediction_reminders_enabled' => 'boolean',
            'game_result_notifications_enabled' => 'boolean',
            'daily_summary_enabled' => 'boolean',
            'tournament_deadline_enabled' => 'boolean',
            'browser_notifications_enabled' => 'boolean',
            'game_reminder_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
