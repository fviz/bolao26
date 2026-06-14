<?php

namespace App\Http\Requests;

use App\Models\Achievement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFeaturedAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is($this->route('user'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('achievementSlug') === '') {
            $this->merge(['achievementSlug' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'achievementSlug' => ['nullable', 'string', 'exists:achievements,slug'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->user()->hasLockedFeaturedAchievement()) {
                $validator->errors()->add(
                    'achievementSlug',
                    __('Sua medalha em destaque está bloqueada.'),
                );

                return;
            }

            $slug = $this->input('achievementSlug');

            if ($slug === null) {
                return;
            }

            $achievement = Achievement::query()->where('slug', $slug)->first();

            if ($achievement === null) {
                return;
            }

            $hasEarned = $this->user()
                ->userAchievements()
                ->where('achievement_id', $achievement->id)
                ->exists();

            if (! $hasEarned) {
                $validator->errors()->add(
                    'achievementSlug',
                    __('Você só pode destacar medalhas que já conquistou.'),
                );
            }
        });
    }
}
