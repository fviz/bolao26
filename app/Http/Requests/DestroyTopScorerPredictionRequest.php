<?php

namespace App\Http\Requests;

use App\Support\TopScorerPredictions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DestroyTopScorerPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->topScorerPrediction !== null;
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
