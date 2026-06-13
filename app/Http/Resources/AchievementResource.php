<?php

namespace App\Http\Resources;

use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Achievement
 */
class AchievementResource extends JsonResource
{
    /**
     * @param  array{earned?: bool, awardedAt?: ?Carbon, progressCurrent?: ?int, progressTarget?: ?int}  $meta
     */
    public function __construct(
        $resource,
        private array $meta = [],
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'emoji' => $this->emoji,
            'tier' => $this->tier->value,
            'tierLabel' => $this->tier->label(),
            'earned' => $this->meta['earned'] ?? false,
            'awardedAt' => isset($this->meta['awardedAt'])
                ? $this->meta['awardedAt']?->toIso8601String()
                : null,
            'progressCurrent' => $this->meta['progressCurrent'] ?? null,
            'progressTarget' => $this->meta['progressTarget'] ?? $this->progress_target,
        ];
    }

    /**
     * @param  array{achievement: Achievement, earned: bool, awardedAt: ?Carbon, progressCurrent: ?int, progressTarget: ?int}  $item
     */
    public static function fromItem(array $item): self
    {
        return new self($item['achievement'], [
            'earned' => $item['earned'],
            'awardedAt' => $item['awardedAt'],
            'progressCurrent' => $item['progressCurrent'],
            'progressTarget' => $item['progressTarget'],
        ]);
    }
}
