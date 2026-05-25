<script setup lang="ts">
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    usePlayerSearch,
    type CountrySearchTerms,
} from '@/composables/usePlayerSearch';
import type { WorldCupPlayer } from '@/types/game';

type Props = {
    players: WorldCupPlayer[];
    countrySearchTerms: CountrySearchTerms;
    modelValue: string | null;
    error?: string;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const searchQuery = ref('');
const playersRef = computed(() => props.players);

const { filteredPlayers } = usePlayerSearch(
    playersRef,
    searchQuery,
    props.countrySearchTerms,
);

function selectPlayer(playerId: string): void {
    emit('update:modelValue', playerId);
}

function formatPlayerLine(player: WorldCupPlayer): string {
    const club = player.club ? ` · ${player.club}` : '';

    return `${player.position}${club} · ${player.country}`;
}
</script>

<template>
    <div class="grid min-w-0 flex-1 gap-3">
        <input type="hidden" name="player_id" :value="modelValue ?? ''" />

        <div class="grid gap-2">
            <Label for="top_scorer_search">Buscar jogador</Label>
            <Input
                id="top_scorer_search"
                v-model="searchQuery"
                type="search"
                placeholder="Nome ou país (ex.: Brasil, Haaland)"
                autocomplete="off"
            />
            <p class="text-muted-foreground text-xs">
                Digite um país em português ou inglês para filtrar a seleção, ou
                o nome do jogador.
            </p>
        </div>

        <div
            class="max-h-64 overflow-y-auto rounded-md border border-input"
            role="listbox"
            aria-label="Jogadores"
        >
            <p
                v-if="filteredPlayers.length === 0"
                class="text-muted-foreground p-3 text-sm"
            >
                Nenhum jogador encontrado.
            </p>
            <button
                v-for="player in filteredPlayers"
                :key="player.id"
                type="button"
                role="option"
                :aria-selected="modelValue === player.id"
                class="hover:bg-accent flex w-full flex-col gap-0.5 border-b border-input px-3 py-2 text-left text-sm last:border-b-0"
                :class="{
                    'bg-accent font-medium': modelValue === player.id,
                }"
                @click="selectPlayer(player.id)"
            >
                <span>{{ player.name }}</span>
                <span class="text-muted-foreground text-xs">
                    {{ formatPlayerLine(player) }}
                </span>
            </button>
        </div>

        <InputError :message="error" />
    </div>
</template>
