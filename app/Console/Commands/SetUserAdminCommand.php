<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:set-admin {user : The user email or ID} {--revoke : Revoke admin status instead of granting it}')]
#[Description('Grant or revoke admin status for a user.')]
class SetUserAdminCommand extends Command
{
    public function handle(): int
    {
        $user = $this->resolveUser($this->argument('user'));

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $isAdmin = ! $this->option('revoke');

        $user->is_admin = $isAdmin;
        $user->save();

        if ($isAdmin) {
            $this->info("Admin granted to {$user->name} ({$user->email}).");
        } else {
            $this->info("Admin revoked from {$user->name} ({$user->email}).");
        }

        return self::SUCCESS;
    }

    private function resolveUser(string $identifier): ?User
    {
        $user = User::query()->where('email', $identifier)->first();

        if ($user !== null) {
            return $user;
        }

        if (is_numeric($identifier)) {
            return User::query()->find((int) $identifier);
        }

        return null;
    }
}
