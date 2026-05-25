export type AppNotification = {
    id: string;
    type: string;
    title: string;
    body: string;
    url: string | null;
    readAt: string | null;
    createdAt: string | null;
};

export type NotificationPaginator<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
};

export type NotificationPreferences = {
    missingPredictionRemindersEnabled: boolean;
    gameResultNotificationsEnabled: boolean;
    dailySummaryEnabled: boolean;
    tournamentDeadlineEnabled: boolean;
    browserNotificationsEnabled: boolean;
    gameReminderMinutes: number;
    dailySummaryTime: string;
    dailySummaryTimezone: string;
};
