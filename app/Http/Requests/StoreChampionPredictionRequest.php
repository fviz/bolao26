<?php

namespace App\Http\Requests;

use App\Support\ChampionPredictions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreChampionPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teamIds = collect(ChampionPredictions::availableTeams())
            ->pluck('fifaTeamId')
            ->all();

        return [
            'fifa_team_id' => ['required', 'string', Rule::in($teamIds)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! ChampionPredictions::isOpen()) {
                $validator->errors()->add(
                    'fifa_team_id',
                    'O prazo para escolher o campeão já encerrou.',
                );
            }
        });
    }
}
