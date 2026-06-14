<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import AchievementEmblem from '@/components/achievements/AchievementEmblem.vue';
import SetFeaturedAchievementButton from '@/components/achievements/SetFeaturedAchievementButton.vue';
import { Button } from '@/components/ui/button';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { show as showUser } from '@/routes/users';
import { index as achievementsIndex } from '@/routes/users/achievements';
import type { Achievement } from '@/types/achievement';
import type { UserProfile } from '@/types/game';

type Props = {
    profile?: UserProfile;
    achievement?: Achievement;
    achievementEarnedPercentage?: number;
};

const props = defineProps<Props>();

const isReady = useHasPageProp('profile');

watchEffect(() => {
    if (!props.profile || !props.achievement) {
        return;
    }

    setLayoutProps({
        breadcrumbs: [
            {
                title: props.profile.isCurrentUser
                    ? 'Perfil'
                    : props.profile.name,
                href: showUser(props.profile.id),
            },
            {
                title: 'Todas Medalhas',
                href: achievementsIndex(props.profile.id),
            },
            {
                title: props.achievement.name,
                href: '#',
            },
        ],
    });
});

const formattedAwardDate = (date: string | null): string | null => {
    if (!date) {
        return null;
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(date));
};
</script>

<template>
    <Head :title="achievement?.name ?? 'Medalha'" />

    <div
        v-if="isReady && profile && achievement"
        class="flex flex-col gap-8 p-4 md:p-6"
    >
        <div
            class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border md:p-10"
        >
            <div class="flex flex-col items-center gap-6 text-center">
                <AchievementEmblem
                    :achievement="achievement"
                    size="lg"
                />

                <div class="space-y-2">
                    <p class="text-sm font-medium uppercase tracking-wide text-muted-foreground">
                        {{ achievement.tierLabel }}
                    </p>
                    <h1 class="text-2xl font-semibold">
                        {{ achievement.name }}
                    </h1>
                    <p class="max-w-md text-muted-foreground">
                        {{ achievement.description }}
                    </p>
                    <p
                        v-if="achievementEarnedPercentage !== undefined"
                        class="text-sm text-muted-foreground"
                    >
                        {{ achievementEarnedPercentage }}% dos usuários conquistaram esta medalha
                    </p>
                </div>

                <p
                    v-if="achievement.earned && achievement.awardedAt"
                    class="text-sm text-green-700 dark:text-green-400"
                >
                    Conquistada em
                    {{ formattedAwardDate(achievement.awardedAt) }}
                </p>
                <p
                    v-else-if="profile.isCurrentUser"
                    class="text-sm text-muted-foreground"
                >
                    Você ainda não conquistou esta medalha.
                </p>
                <p v-else class="text-sm text-muted-foreground">
                    {{ profile.name }} ainda não conquistou esta medalha.
                </p>

                <div
                    v-if="
                        !achievement.earned
                        && achievement.progressTarget !== null
                        && achievement.progressCurrent !== null
                    "
                    class="w-full max-w-xs space-y-2"
                >
                    <div class="h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary transition-all"
                            :style="{
                                width: `${Math.min(100, (achievement.progressCurrent / achievement.progressTarget) * 100)}%`,
                            }"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Progresso: {{ achievement.progressCurrent }} /
                        {{ achievement.progressTarget }}
                    </p>
                </div>

                <SetFeaturedAchievementButton
                    v-if="profile.isCurrentUser && achievement.earned"
                    :user-id="profile.id"
                    :achievement-slug="achievement.slug"
                    :is-featured="achievement.isFeatured ?? false"
                    :earned="achievement.earned"
                    :locked="profile.featuredAchievementLocked ?? false"
                    set-label="Exibir ao lado do meu nome"
                />
            </div>
        </div>

        <Button as-child variant="outline" class="self-center">
            <Link :href="achievementsIndex(profile.id)">
                Ver todas as medalhas
            </Link>
        </Button>
    </div>
</template>
