<script setup lang="ts">
import { Form, Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import GameMatchDisplay from '@/components/games/GameMatchDisplay.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useGameSchedule } from '@/composables/useGameSchedule';
import { dashboard } from '@/routes';
import { upsert as upsertPrediction } from '@/routes/games/prediction';
import type { GameListItem } from '@/types/game';

type Props = {
    game: GameListItem;
};

const props = defineProps<Props>();

const { formatScheduledAt } = useGameSchedule();
const schedule = formatScheduledAt(props.game.scheduledAt);

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [
            {
                title: 'Dashboard do Bolão',
                href: dashboard(),
            },
            {
                title: props.game.matchTitle,
            },
        ],
    });
});

const venueLabel = (): string => {
    if (props.game.stadiumName && props.game.cityName) {
        return `${props.game.stadiumName}, ${props.game.cityName}`;
    }

    return props.game.stadiumName ?? props.game.cityName ?? '—';
};
</script>

<template>
    <Head :title="game.matchTitle" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <Heading
            variant="small"
            :title="game.matchTitle"
            :description="
                [game.stageName, game.groupName].filter(Boolean).join(' · ') ||
                undefined
            "
        />

        <section
            class="flex flex-col gap-4 rounded-xl border p-4 md:p-6"
        >
            <GameMatchDisplay
                :home="game.home"
                :away="game.away"
                layout="stacked"
            />
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">Data e hora</dt>
                    <dd class="font-medium">{{ schedule.combined }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Estádio</dt>
                    <dd class="font-medium">{{ venueLabel() }}</dd>
                </div>
                <div v-if="game.matchNumber">
                    <dt class="text-muted-foreground">Jogo</dt>
                    <dd class="font-medium">#{{ game.matchNumber }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border p-4 md:p-6">
            <Heading
                variant="small"
                title="Sua previsão"
                :description="
                    game.isBettingOpen
                        ? 'Apostas aceitas até 1 minuto antes do apito inicial'
                        : 'Apostas encerradas para este jogo'
                "
            />

            <div
                v-if="!game.isBettingOpen"
                class="mt-4 space-y-3"
            >
                <p
                    v-if="game.userPrediction"
                    class="text-lg font-semibold"
                >
                    {{ game.userPrediction.homeScore }} ×
                    {{ game.userPrediction.awayScore }}
                </p>
                <p
                    v-else
                    class="text-muted-foreground text-sm"
                >
                    Você não registrou previsão para este jogo.
                </p>
                <p class="text-muted-foreground text-sm">
                    O prazo encerrou em
                    {{
                        formatScheduledAt(game.bettingClosesAt).combined
                    }}.
                </p>
            </div>

            <Form
                v-else
                v-bind="upsertPrediction.form(game.id)"
                class="mt-4 space-y-4"
                v-slot="{ errors, processing }"
            >
                <div class="flex flex-wrap items-end gap-4">
                    <div class="grid gap-2">
                        <Label :for="`home_score_${game.id}`">{{
                            game.home.displayName
                        }}</Label>
                        <Input
                            :id="`home_score_${game.id}`"
                            name="home_score"
                            type="number"
                            min="0"
                            max="20"
                            class="w-24"
                            required
                            :default-value="
                                game.userPrediction?.homeScore ?? 0
                            "
                        />
                        <InputError :message="errors.home_score" />
                    </div>
                    <span class="text-muted-foreground pb-2 text-lg font-medium"
                        >×</span
                    >
                    <div class="grid gap-2">
                        <Label :for="`away_score_${game.id}`">{{
                            game.away.displayName
                        }}</Label>
                        <Input
                            :id="`away_score_${game.id}`"
                            name="away_score"
                            type="number"
                            min="0"
                            max="20"
                            class="w-24"
                            required
                            :default-value="
                                game.userPrediction?.awayScore ?? 0
                            "
                        />
                        <InputError :message="errors.away_score" />
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Button type="submit" :disabled="processing">
                        Salvar previsão
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="dashboard()">Voltar ao dashboard</Link>
                    </Button>
                </div>
            </Form>
        </section>
    </div>
</template>
