<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { rules } from '@/routes';
import Heading from '@/components/Heading.vue';

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
                        Boa previsão do resultado completo<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 2-1 previsto, resultado 2-1</span
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
                        Boa previsão de empate<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 1-1 previsto, resultado 2-2</span
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
                        Boa previsão do vencedor + número de gols de um
                        time<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 3-0 previsto, resultado 3-1</span
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
                        Boa previsão do vencedor<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 3-1 previsto, resultado 1-0</span
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
                        Boa previsão do número de gols de um time<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 1-2 previsto, resultado 1-0</span
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
            description="Quando o jogo for decidido nos pênaltis e você prever empate no tempo regulamentar"
        />

        <table class="w-full border-separate rounded-xl">
            <tbody class="rounded-xl">
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="py-2 pl-4">
                        Previsão perfeita de empate e vencedor nos
                        pênaltis<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 2-2 e time B nos pênaltis previsto,
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
                        Boa previsão de empate e vencedor nos pênaltis<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 2-2 e time B nos pênaltis previsto,
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
                        Boa previsão de empate, mas erro no vencedor nos
                        pênaltis<br />
                        <span class="text-sm text-gray-500"
                            >Por exemplo, 2-2 e time B nos pênaltis previsto,
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
            description="Escolha o campeão antes do início da Copa"
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
            </tbody>
        </table>
    </div>
</template>
