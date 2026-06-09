<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { rules } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Regras do Bolão',
                href: rules(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Regras do Bolão" />

    <div class="flex flex-col gap-4 p-4">
        <h1 class="sr-only">Regras do Bolão</h1>

        <div class="space-y-6">
            <Heading
                variant="small"
                title="Regras do Bolão"
                description="Pontuação por jogo e bônus de campeão"
            />
        </div>

        <table class="w-full border-separate rounded-xl">
            <tbody class="rounded-xl">
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="py-2 pl-4">
                        Bom palpite do resultado completo<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 2-1, resultado 2-1</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            200 pontos
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="pl-4">
                        Bom palpite de empate<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 1-1, resultado 2-2</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            100 pontos
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="pl-4">
                        Bom palpite do vencedor + número de gols de um
                        time<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 3-0, resultado 3-1</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            95 pontos
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="pl-4">
                        Bom palpite do vencedor<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 3-1, resultado 1-0</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            75 pontos
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="pl-4">
                        Bom palpite do número de gols de um time<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 1-2, resultado 1-0</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            20 pontos
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <Heading
            variant="small"
            title="Fases eliminatórias"
            description="Quando o jogo for decidido nos pênaltis e você palpitar empate no tempo regulamentar"
        />

        <table class="w-full border-separate rounded-xl">
            <tbody class="rounded-xl">
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="py-2 pl-4">
                        Palpite perfeito de empate e vencedor nos
                        pênaltis<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 2-2 e time B nos pênaltis,
                            resultado 2-2 e time B nos pênaltis</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            220 pontos
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="pl-4">
                        Bom palpite de empate e vencedor nos pênaltis<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 2-2 e time B nos pênaltis,
                            resultado 3-3 e time B nos pênaltis</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            120 pontos
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="pl-4">
                        Bom palpite de empate, mas erro no vencedor nos
                        pênaltis<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, palpite de 2-2 e time B nos pênaltis,
                            resultado 2-2 e time A nos pênaltis</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            50 pontos
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <Heading
            variant="small"
            title="Fim do campeonato"
            description="Escolha o campeão e o artilheiro antes do início da Copa"
        />

        <table class="w-full border-separate rounded-xl">
            <tbody class="rounded-xl">
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="py-2 pl-4">
                        Acertar o campeão<br />
                        <span class="text-sm text-gray-500"
                            >Seleção vencedora da final</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            300 pontos
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="pl-4">
                        Acertar o artilheiro<br />
                        <span class="text-sm text-gray-500"
                            >Jogador com mais gols na Copa (definido ao fim do
                            campeonato)</span
                        >
                    </td>
                    <td class="p-4">
                        <div
                            class="rounded-md bg-green-500 p-2 text-center font-bold text-white"
                        >
                            300 pontos
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
