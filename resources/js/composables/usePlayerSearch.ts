import { computed, type Ref } from 'vue';
import type { WorldCupPlayer } from '@/types/game';

export type CountrySearchTerms = Record<string, string[]>;

function normalizeSearchText(value: string): string {
    return value
        .normalize('NFD')
        .replace(/\p{M}/gu, '')
        .toLowerCase()
        .trim();
}

function matchesCountryQuery(
    query: string,
    countrySearchTerms: CountrySearchTerms,
): string | null {
    const normalizedQuery = normalizeSearchText(query);

    if (normalizedQuery === '') {
        return null;
    }

    for (const [countryKey, aliases] of Object.entries(countrySearchTerms)) {
        for (const alias of aliases) {
            const normalizedAlias = normalizeSearchText(alias);

            if (
                normalizedQuery.includes(normalizedAlias) ||
                normalizedAlias.includes(normalizedQuery)
            ) {
                return countryKey;
            }
        }
    }

    return null;
}

export function usePlayerSearch(
    players: Ref<WorldCupPlayer[]>,
    query: Ref<string>,
    countrySearchTerms: CountrySearchTerms,
) {
    const filteredPlayers = computed(() => {
        const trimmedQuery = query.value.trim();

        if (trimmedQuery === '') {
            return players.value;
        }

        const countryKey = matchesCountryQuery(
            trimmedQuery,
            countrySearchTerms,
        );

        if (countryKey !== null) {
            return players.value.filter(
                (player) => player.countryKey === countryKey,
            );
        }

        const normalizedQuery = normalizeSearchText(trimmedQuery);

        return players.value.filter((player) =>
            normalizeSearchText(player.name).includes(normalizedQuery),
        );
    });

    return { filteredPlayers, normalizeSearchText };
}
