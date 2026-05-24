<?php

namespace App\Http\Requests;

use App\Models\Game;
use App\Support\PenaltyWinner;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePredictionRequest extends FormRequest
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
            'home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'away_score' => ['required', 'integer', 'min:0', 'max:20'],
            'penalty_winner' => [
                'nullable',
                'string',
                Rule::in(PenaltyWinner::values()),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Game $game */
            $game = $this->route('game');

            if (! $game->isBettingOpen()) {
                $validator->errors()->add(
                    'home_score',
                    'Apostas encerradas — o prazo termina 1 minuto antes do apito inicial.',
                );
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $homeScore = (int) $this->input('home_score');
            $awayScore = (int) $this->input('away_score');
            $isDraw = $homeScore === $awayScore;

            if ($game->isKnockout() && $isDraw && $this->input('penalty_winner') === null) {
                $validator->errors()->add(
                    'penalty_winner',
                    'Informe o vencedor nos pênaltis quando prever empate em jogo eliminatório.',
                );
            }

            if ($game->isGroupStage() && $this->input('penalty_winner') !== null) {
                $validator->errors()->add(
                    'penalty_winner',
                    'Pênaltis só se aplicam à fase eliminatória.',
                );
            }

            if ($game->isKnockout() && ! $isDraw && $this->input('penalty_winner') !== null) {
                $validator->errors()->add(
                    'penalty_winner',
                    'Vencedor nos pênaltis só é necessário quando a previsão for empate.',
                );
            }
        });
    }

    public function validated($key = null, $default = null): mixed
    {
        if ($key !== null) {
            return parent::validated($key, $default);
        }

        $validated = parent::validated();

        /** @var Game $game */
        $game = $this->route('game');

        if (
            $game->isGroupStage()
            || (int) $validated['home_score'] !== (int) $validated['away_score']
        ) {
            $validated['penalty_winner'] = null;
        }

        return $validated;
    }
}
