<?php

namespace App\Notifications;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class AchievementEarned extends Notification implements ShouldQueue
{
    use ChoosesNotificationChannels, Queueable;

    /**
     * @param  list<Achievement>  $achievements
     */
    public function __construct(
        public User $user,
        public array $achievements,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toWebPush(User $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title())
            ->body($this->body())
            ->icon('/logo.png')
            ->badge('/favicon.svg')
            ->action('Ver medalhas', 'open_achievements')
            ->data(['url' => $this->url()])
            ->options(['TTL' => 86400]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'type' => 'achievement_earned',
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => $this->url(),
            'achievement_slugs' => array_map(
                fn (Achievement $achievement) => $achievement->slug,
                $this->achievements,
            ),
            'achievement_count' => count($this->achievements),
        ];
    }

    private function title(): string
    {
        return count($this->achievements) > 1 ? 'Novas medalhas!' : 'Nova medalha!';
    }

    private function body(): string
    {
        $first = $this->achievements[0];
        $extra = count($this->achievements) - 1;

        if ($extra === 0) {
            return "Você ganhou a medalha {$first->name} {$first->emoji}.";
        }

        return "Você ganhou a medalha {$first->name} {$first->emoji} e mais {$this->additionalMedalWord($extra)}.";
    }

    private function additionalMedalWord(int $count): string
    {
        return match ($count) {
            1 => 'uma',
            2 => 'duas',
            3 => 'três',
            4 => 'quatro',
            5 => 'cinco',
            6 => 'seis',
            7 => 'sete',
            8 => 'oito',
            9 => 'nove',
            10 => 'dez',
            default => (string) $count,
        };
    }

    private function url(): string
    {
        return route('users.achievements.index', $this->user, false);
    }
}
