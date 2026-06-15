export type GameDayGroup<T extends { scheduledAt: string | null }> = {
    key: string;
    label: string;
    games: T[];
};

export function useGameSchedule() {
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    const formatScheduledAt = (
        iso: string | null | undefined,
    ): { date: string; time: string; combined: string } => {
        if (!iso) {
            return { date: '—', time: '', combined: '—' };
        }

        const dateValue = new Date(iso);
        const date = new Intl.DateTimeFormat('pt-BR', {
            dateStyle: 'short',
            timeZone,
        }).format(dateValue);
        const time = new Intl.DateTimeFormat('pt-BR', {
            timeStyle: 'short',
            timeZone,
        }).format(dateValue);

        return {
            date,
            time,
            combined: `${date} ${time}`,
        };
    };

    const dayKeyForScheduledAt = (
        iso: string | null | undefined,
    ): string => {
        if (!iso) {
            return 'unknown';
        }

        return new Intl.DateTimeFormat('en-CA', {
            timeZone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).format(new Date(iso));
    };

    const formatDayHeading = (iso: string | null | undefined): string => {
        if (!iso) {
            return '—';
        }

        return new Intl.DateTimeFormat('pt-BR', {
            day: 'numeric',
            month: 'long',
            timeZone,
        }).format(new Date(iso));
    };

    const groupGamesByDay = <T extends { scheduledAt: string | null }>(
        games: T[],
    ): GameDayGroup<T>[] => {
        const groups = new Map<string, GameDayGroup<T>>();

        for (const game of games) {
            const key = dayKeyForScheduledAt(game.scheduledAt);

            if (!groups.has(key)) {
                groups.set(key, {
                    key,
                    label: formatDayHeading(game.scheduledAt),
                    games: [],
                });
            }

            groups.get(key)!.games.push(game);
        }

        return Array.from(groups.values());
    };

    return {
        formatScheduledAt,
        formatDayHeading,
        groupGamesByDay,
        timeZone,
    };
}
