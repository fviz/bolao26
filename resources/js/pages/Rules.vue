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
                description="As regras do Bolão"
            />
        </div>

        <table class="w-full border-separate rounded-xl">
            <tbody class="rounded-xl">
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="pl-4 py-2">Boa previsão do resultado completo<br />
                        <span class="text-sm text-gray-500">Por exemplo, 2-1 previsto, resultado 2-1</span></td>
                    <td class="p-4">
                        <div class="bg-green-500 rounded-md text-white font-bold text-center p-2">200 pontos</div>
                    </td>
                </tr>
                <tr>
                    <td class="pl-4">Boa previsão de empate<br />
                        <span class="text-sm text-gray-500">Por exemplo, 1-1 previsto, resultado 2-2</span></td>
                    <td class="p-4">
                        <div class="bg-green-500 rounded-md text-white font-bold text-center p-2">100 pontos</div>
                    </td>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="pl-4">Boa previsão do vencedor + número de gols de um time<br />
                        <span class="text-sm text-gray-500">Por exemplo, 3-0 previsto, resultado 3-1</span></td>
                    <td class="p-4">
                        <div class="bg-green-500 rounded-md text-white font-bold text-center p-2">95 pontos</div>
                    </td>
                </tr>
                <tr>
                    <td class="pl-4">Boa previsão do vencedor<br />
                        <span class="text-sm text-gray-500">Por exemplo, 3-1 previsto, resultado 1-0</span></td>
                    <td class="p-4">
                        <div class="bg-green-500 rounded-md text-white font-bold text-center p-2">75 pontos</div>
                    </td>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-900">
                    <td class="pl-4">Boa previsão do número de gols de um time<br />
                        <span class="text-sm text-gray-500">Por exemplo, 1-2 previsto, resultado 1-0</span></td>
                    <td class="p-4">
                        <div class="bg-green-500 rounded-md text-white font-bold text-center p-2">20 pontos</div>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</template>