<script setup lang="ts">
import { Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import AchievementGrid from '@/components/achievements/AchievementGrid.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { show as showUser } from '@/routes/users';
import { index as achievementsIndex } from '@/routes/users/achievements';
import type { Achievement } from '@/types/achievement';
import type { UserProfile } from '@/types/game';

type SortOption = 'catalog' | 'name' | 'awarded';

type Props = {
    profile?: UserProfile;
    achievements?: Achievement[];
    sort?: SortOption;
};

const props = defineProps<Props>();

const isReady = useHasPageProp('profile');

const sortOptions: { value: SortOption; label: string }[] = [
    { value: 'catalog', label: 'Padrão' },
    { value: 'name', label: 'Nome' },
    { value: 'awarded', label: 'Última conquistada' },
];

watchEffect(() => {
    if (!props.profile) {
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
        ],
    });
});

const updateSort = (value: unknown) => {
    if (!props.profile || typeof value !== 'string') {
        return;
    }

    router.get(
        achievementsIndex(props.profile.id, { query: { sort: value } }).url,
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <Head title="Todas Medalhas" />

    <div v-if="isReady && profile && achievements" class="flex flex-col gap-8 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <Heading
                variant="small"
                title="Todas Medalhas"
                :description="
                    profile.isCurrentUser
                        ? 'Suas medalhas e progresso no bolão.'
                        : `Medalhas de ${profile.name}.`
                "
            />
            <Button as-child variant="outline" class="shrink-0">
                <Link :href="showUser(profile.id)">Voltar ao perfil</Link>
            </Button>
        </div>

        <section
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
        >
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Label for="achievement-sort" class="text-sm text-muted-foreground">
                    Ordenar por
                </Label>
                <Select
                    :model-value="sort ?? 'catalog'"
                    @update:model-value="updateSort"
                >
                    <SelectTrigger id="achievement-sort" class="w-full sm:w-56">
                        <SelectValue placeholder="Ordenar por" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in sortOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <AchievementGrid
                :achievements="achievements"
                :user-id="profile.id"
                show-progress
            />
        </section>
    </div>
</template>
