export type GameTeam = {
    displayName: string;
    abbr: string | null;
    flagEmoji: string | null;
};

export type UserPrediction = {
    homeScore: number;
    awayScore: number;
    penaltyWinner: string | null;
    points: number | null;
};

export type GameResult = {
    homeScore: number;
    awayScore: number;
    penaltyWinner: string | null;
};

export type GamePredictionEntry = {
    userId: number;
    userName: string;
    homeScore: number;
    awayScore: number;
    penaltyWinner: string | null;
    points: number | null;
    isCurrentUser: boolean;
};

export type GameListItem = {
    id: number;
    matchTitle: string;
    matchNumber: number | null;
    stageName: string | null;
    groupName: string | null;
    scheduledAt: string | null;
    localScheduledAt: string | null;
    stadiumName: string | null;
    cityName: string | null;
    home: GameTeam;
    away: GameTeam;
    isKnockout: boolean;
    isFinal: boolean;
    isBettingOpen: boolean;
    arePredictionsVisible: boolean;
    bettingClosesAt: string;
    result?: GameResult;
    userPrediction?: UserPrediction | null;
    allPredictions?: GamePredictionEntry[];
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: Array<{
            url: string | null;
            label: string;
            page: number | null;
            active: boolean;
        }>;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
};

export type LeaderboardEntry = {
    id: number;
    name: string;
    totalPoints: number;
    rank: number;
    isCurrentUser: boolean;
};

export type ChampionTeam = {
    fifaTeamId: string;
    name: string;
    abbr: string | null;
};

export type ChampionPrediction = {
    fifaTeamId: string;
    points: number | null;
};
