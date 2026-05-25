<?php

namespace App\Http\Requests;

use App\Support\ChampionPredictions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DestroyChampionPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->championPrediction !== null;
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
