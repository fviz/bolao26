<?php

namespace App\Http\Requests;

use App\Models\Game;
use App\Models\GameComment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreGameCommentRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:250'],
            'parent_id' => ['nullable', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $parentId = $this->input('parent_id');

            if ($parentId === null) {
                return;
            }

            /** @var Game $game */
            $game = $this->route('game');

            /** @var GameComment|null $parent */
            $parent = GameComment::query()
                ->where('id', $parentId)
                ->where('game_id', $game->id)
                ->first();

            if ($parent === null) {
                $validator->errors()->add(
                    'parent_id',
                    'O comentário referenciado não existe neste jogo.',
                );

                return;
            }

            if (! $parent->isTopLevel()) {
                $validator->errors()->add(
                    'parent_id',
                    'Só é possível responder a comentários de primeiro nível.',
                );
            }
        });
    }
}
