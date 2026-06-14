export type AchievementTier =
    | 'lixo_humano'
    | 'bronze'
    | 'silver'
    | 'gold'
    | 'diamond';

export type AchievementSummary = {
    earned: number;
    total: number;
};

export type FeaturedAchievement = {
    slug: string;
    name: string;
    emoji: string;
    tier: AchievementTier;
};

export type Achievement = {
    slug: string;
    name: string;
    description: string;
    emoji: string;
    tier: AchievementTier;
    tierLabel: string;
    earned: boolean;
    awardedAt: string | null;
    progressCurrent: number | null;
    progressTarget: number | null;
    isFeatured?: boolean;
};
