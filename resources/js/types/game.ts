export type GameTeam = {
    displayName: string;
    abbr: string | null;
    flagEmoji: string | null;
};

export type UserPrediction = {
    homeScore: number;
    awayScore: number;
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
    isBettingOpen: boolean;
    bettingClosesAt: string;
    userPrediction?: UserPrediction | null;
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
