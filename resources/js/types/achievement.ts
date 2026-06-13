export type AchievementTier = 'bronze' | 'silver' | 'gold' | 'diamond';

export type AchievementSummary = {
    earned: number;
    total: number;
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
};
