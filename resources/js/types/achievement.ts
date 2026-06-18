import type { GameTeam } from '@/types/game';

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

export type AchievementAwardGame = {
    id: number;
    matchTitle: string;
    stageName: string | null;
    home: GameTeam;
    away: GameTeam;
};

export type AchievementAwardTeam = {
    name: string;
    abbr: string | null;
    flagIconCode: string | null;
};

export type AchievementAwardPlayer = {
    id: string;
    name: string;
};

export type AchievementAwardContext =
    | {
          type: 'game';
          trigger: 'after_match' | 'on_prediction';
          game: AchievementAwardGame;
      }
    | {
          type: 'match_day';
          matchDay: string;
          games: AchievementAwardGame[];
      }
    | {
          type: 'champion';
          team: AchievementAwardTeam;
      }
    | {
          type: 'top_scorer';
          player: AchievementAwardPlayer;
      }
    | {
          type: 'champion_and_top_scorer';
          team: AchievementAwardTeam;
          player: AchievementAwardPlayer;
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
    awardContext?: AchievementAwardContext | null;
};
