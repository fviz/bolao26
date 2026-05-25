<?php

namespace App\Http\Requests;

use App\Models\GameComment;
use Illuminate\Foundation\Http\FormRequest;

class DestroyGameCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var GameComment $comment */
        $comment = $this->route('comment');

        return $this->user()?->id === $comment->user_id;
    }
}
