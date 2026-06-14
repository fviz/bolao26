<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Achievements\AchievementCatalog;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[Fillable(['name', 'email', 'password', 'total_points'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'avatar_path'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable, TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $appends = [
        'avatar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'total_points' => 'integer',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->avatar_path === null) {
                return null;
            }

            return Storage::disk('public')->url($this->avatar_path);
        });
    }

    public function deleteAvatarFile(): void
    {
        if ($this->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->avatar_path);

        $this->avatar_path = null;
        $this->save();
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function championPrediction(): HasOne
    {
        return $this->hasOne(ChampionPrediction::class);
    }

    public function topScorerPrediction(): HasOne
    {
        return $this->hasOne(TopScorerPrediction::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function gameComments(): HasMany
    {
        return $this->hasMany(GameComment::class);
    }

    public function featuredAchievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'featured_achievement_id');
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot(['awarded_at', 'context'])
            ->withTimestamps();
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function achievementProgress(): HasMany
    {
        return $this->hasMany(UserAchievementProgress::class);
    }

    public function hasLockedFeaturedAchievement(): bool
    {
        $achievementId = AchievementCatalog::id('traidor-da-patria');

        if ($achievementId === null) {
            return false;
        }

        return $this->userAchievements()
            ->where('achievement_id', $achievementId)
            ->exists();
    }
}
