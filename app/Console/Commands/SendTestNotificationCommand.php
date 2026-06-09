<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestUserNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notifications:send-test {email : The recipient user email} {body : The notification body text}')]
#[Description('Send a test notification to a single user by email.')]
class SendTestNotificationCommand extends Command
{
    public function handle(): int
    {
        $user = User::query()
            ->where('email', $this->argument('email'))
            ->first();

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $body = $this->argument('body');

        $user->notifyNow(new TestUserNotification($body));

        $this->info("Test notification sent to {$user->name} ({$user->email}).");

        return self::SUCCESS;
    }
}
