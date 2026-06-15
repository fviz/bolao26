<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { watchEffect } from 'vue';
import AchievementGrid from '@/components/achievements/AchievementGrid.vue';
import AchievementEmblem from '@/components/achievements/AchievementEmblem.vue';
import FeaturedAchievementBadge from '@/components/achievements/FeaturedAchievementBadge.vue';
import FeaturedAchievementPicker from '@/components/achievements/FeaturedAchievementPicker.vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import Heading from '@/components/Heading.vue';
import LoadMoreList from '@/components/LoadMoreList.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { useInitials } from '@/composables/useInitials';
import { show as showGame } from '@/routes/games';
import { edit as editProfile } from '@/routes/profile';
import { show as showUser } from '@/routes/users';
import { index as achievementsIndex } from '@/routes/users/achievements';
import type { Achievement, AchievementSummary } from '@/types/achievement';
import type { Paginated, ProfileGameEntry, UserProfile } from '@/types/game';

type Props = {
    profile?: UserProfile;
    finishedGames?: Paginated<ProfileGameEntry>;
    earnedAchievements?: Achievement[];
    achievementSummary?: AchievementSummary;
};

const props = defineProps<Props>();

const isReady = useHasPageProp('profile');
const { formatScheduledAt } = useGameSchedule();
const { getInitials } = useInitials();

watchEffect(() => {
    if (!props.profile) {
        return;
    }

    setLayoutProps({
        breadcrumbs: [
            {
                title: props.profile.isCurrentUser ? 'Perfil' : props.profile.name,
                href: showUser(props.profile.id),
            },
        ],
    });
});

const penaltyWinnerLabel = (
    game: ProfileGameEntry,
    side: string | null,
): string => {
    if (side === 'home') {
        return game.home.displayName;
    }

    if (side === 'away') {
        return game.away.displayName;
    }

    return '—';
};

const achievementSummaryDescription = (
    summary: AchievementSummary,
    profile: UserProfile,
): string => {
    const count = `${summary.earned} de ${summary.total} medalhas conquistadas`;

    if (profile.isCurrentUser) {
        return count;
    }

    return `${count} por ${profile.name}`;
};
</script>

<template>
    <Head :title="profile?.name ?? 'Perfil'" />

    <div v-if="isReady && profile" class="flex flex-col gap-8 p-4 md:p-6">
        <div
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Avatar class="size-20 border bg-muted">
                        <AvatarImage
                            v-if="profile.avatar"
                            :src="profile.avatar"
                            :alt="profile.name"
                        />
                        <AvatarFallback
                            class="rounded-full text-lg font-medium text-black dark:text-white"
                        >
                            {{ getInitials(profile.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="min-w-0 space-y-1">
                        <h1 class="inline-flex items-center gap-2 text-xl font-semibold">
                            <span>{{ profile.name }}</span>
                            <FeaturedAchievementBadge
                                v-if="profile.featuredAchievement"
                                :achievement="profile.featuredAchievement"
                                size="md"
                            />
                            <span
                                v-if="profile.isCurrentUser"
                                class="font-normal text-muted-foreground"
                            >
                                (você)
                            </span>
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{ profile.rank }}º lugar ·
                            {{ profile.totalPoints }} pontos
                        </p>
                    </div>
                </div>

                <Button
                    v-if="profile.isCurrentUser"
                    as-child
                    variant="outline"
                    class="shrink-0"
                >
                    <Link :href="editProfile()">
                        <Settings class="size-4" />
                        Ajustes
                    </Link>
                </Button>
            </div>
        </div>

        <section
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <Heading
                    variant="small"
                    title="Medalhas"
                    :description="
                        achievementSummary
                            ? achievementSummaryDescription(achievementSummary, profile)
                            : 'Conquistas no bolão'
                    "
                />
                <Button as-child variant="outline" size="sm" class="shrink-0">
                    <Link :href="achievementsIndex(profile.id)">
                        Todas Medalhas
                    </Link>
                </Button>
            </div>

            <div
                v-if="profile.isCurrentUser"
                class="mt-4 rounded-lg border border-dashed border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <template v-if="profile.featuredAchievement">
                            <AchievementEmblem
                                :achievement="{
                                    ...profile.featuredAchievement,
                                    earned: true,
                                    progressCurrent: null,
                                    progressTarget: null,
                                }"
                                size="sm"
                            />
                            <div>
                                <p class="text-sm font-medium">
                                    Medalha em destaque
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ profile.featuredAchievement.name }}
                                </p>
                            </div>
                        </template>
                        <p v-else class="text-sm text-muted-foreground">
                            Nenhuma medalha em destaque
                        </p>
                    </div>
                    <FeaturedAchievementPicker
                        v-if="earnedAchievements?.length && !profile.featuredAchievementLocked"
                        :user-id="profile.id"
                        :earned-achievements="earnedAchievements"
                    />
                </div>
                <p
                    v-if="profile.featuredAchievementLocked"
                    class="mt-3 text-sm text-muted-foreground"
                >
                    Sua medalha em destaque está bloqueada por conquistar Traidor da Pátria.
                </p>
            </div>

            <AchievementGrid
                v-if="earnedAchievements?.length"
                class="mt-4"
                :achievements="earnedAchievements"
                :user-id="profile.id"
            />
            <p v-else class="mt-4 text-sm text-muted-foreground">
                Nenhuma medalha conquistada ainda.
            </p>
        </section>

        <section
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
        >
            <Heading
                variant="small"
                title="Histórico de palpites"
                description="Palpites em jogos finalizados"
            />

            <LoadMoreList
                v-if="finishedGames?.data.length"
                data="finishedGames"
                class="mt-4"
            >
                <div class="flex flex-col gap-4">
                <Link
                    v-for="game in finishedGames.data"
                    :key="game.id"
                    :href="showGame(game.id)"
                    class="group block rounded-lg p-3 transition-colors odd:bg-gray-100 hover:bg-gray-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 dark:odd:bg-gray-900/30 dark:hover:bg-gray-800"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div class="min-w-0 space-y-2">
                            <GameMatchDisplay
                                :home="game.home"
                                :away="game.away"
                            />
                            <p class="text-xs text-muted-foreground">
                                <span v-if="game.stageName">
                                    {{ game.stageName }} ·
                                </span>
                                {{
                                    game.scheduledAt
                                        ? formatScheduledAt(game.scheduledAt)
                                              .combined
                                        : '—'
                                }}
                            </p>
                        </div>

                        <div
                            class="shrink-0 space-y-1 text-sm sm:text-right"
                        >
                            <p v-if="game.prediction">
                                <span class="text-muted-foreground"
                                    >Palpite:</span
                                >
                                {{ game.prediction.homeScore }} ×
                                {{ game.prediction.awayScore }}
                                <span
                                    v-if="game.prediction.penaltyWinner"
                                    class="block text-xs text-muted-foreground sm:inline"
                                >
                                    (pênaltis:
                                    {{
                                        penaltyWinnerLabel(
                                            game,
                                            game.prediction.penaltyWinner,
                                        )
                                    }})
                                </span>
                            </p>
                            <p>
                                <span class="text-muted-foreground"
                                    >Resultado:</span
                                >
                                {{ game.result.homeScore }} ×
                                {{ game.result.awayScore }}
                            </p>
                            <p
                                v-if="game.prediction?.points !== null && game.prediction?.points !== undefined"
                                class="font-medium text-green-700 dark:text-green-400"
                            >
                                +{{ game.prediction.points }} pontos
                            </p>
                            <p
                                v-else
                                class="text-muted-foreground"
                            >
                                —
                            </p>
                        </div>
                    </div>
                </Link>

                </div>
            </LoadMoreList>

            <p v-else class="mt-4 text-sm text-muted-foreground">
                Nenhum palpite em jogos finalizados ainda.
            </p>
        </section>
    </div>
</template>
