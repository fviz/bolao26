<?php

namespace App\Http\Resources;

use App\Models\GameComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GameComment
 */
class GameCommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = [
            'id' => $this->id,
            'body' => $this->body,
            'createdAt' => $this->created_at?->toIso8601String(),
            'userId' => $this->user_id,
            'userName' => $this->user->name,
            'userAvatar' => $this->user->avatar,
            'isCurrentUser' => $request->user()?->id === $this->user_id,
        ];

        if ($this->isTopLevel() && $this->relationLoaded('replies')) {
            $payload['replies'] = self::collection($this->replies)->resolve($request);
        } else {
            $payload['replies'] = [];
        }

        return $payload;
    }
}
