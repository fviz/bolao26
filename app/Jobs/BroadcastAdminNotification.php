<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastAdminNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
    ) {}

    public function handle(): void
    {
        User::query()->each(function (User $user): void {
            $user->notify(new AdminBroadcastNotification(
                $this->title,
                $this->body,
                $this->url,
            ));
        });
    }
}
