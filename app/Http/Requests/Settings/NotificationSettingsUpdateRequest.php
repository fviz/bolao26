<?php

namespace App\Http\Requests\Settings;

use App\Models\NotificationPreference;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationSettingsUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'missing_prediction_reminders_enabled' => ['required', 'boolean'],
            'game_result_notifications_enabled' => ['required', 'boolean'],
            'daily_summary_enabled' => ['required', 'boolean'],
            'tournament_deadline_enabled' => ['required', 'boolean'],
            'browser_notifications_enabled' => ['required', 'boolean'],
            'game_reminder_minutes' => ['required', 'integer', Rule::in(NotificationPreference::GAME_REMINDER_MINUTES)],
            'daily_summary_time' => ['required', 'date_format:H:i'],
            'daily_summary_timezone' => ['required', 'string', Rule::in(DateTimeZone::listIdentifiers())],
        ];
    }
}
