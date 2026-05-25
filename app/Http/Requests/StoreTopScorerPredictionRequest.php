<?php

namespace App\Http\Requests;

use App\Support\TopScorerPredictions;
use App\Support\WorldCupPlayers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTopScorerPredictionRequest extends FormRequest
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
        return [
            'player_id' => ['required', 'string', Rule::in(WorldCupPlayers::ids())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! TopScorerPredictions::isOpen()) {
                $validator->errors()->add(
                    'player_id',
                    'O prazo para escolher o artilheiro já encerrou.',
                );
            }
        });
    }
}
