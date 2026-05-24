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
            dateStyle: 'medium',
            timeZone,
        }).format(dateValue);
        const time = new Intl.DateTimeFormat('pt-BR', {
            timeStyle: 'short',
            timeZone,
        }).format(dateValue);

        return {
            date,
            time,
            combined: `${date} às ${time}`,
        };
    };

    return { formatScheduledAt, timeZone };
}
