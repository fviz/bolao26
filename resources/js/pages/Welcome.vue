<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Bem-vindo">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="welcome-page flex min-h-screen flex-col items-center p-6 text-[#1b1b18] lg:justify-center lg:p-8"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Painel
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Entrar
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Cadastrar
                    </Link>
                </template>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
            >
                <div
                    class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h1 class="mb-1 font-medium">Bolão 2026!</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        Chegou o segundo momento mais aguardado do ano (depois do lançamento do GTA VI). <br /><br />É época de Copa do Mundo, e nada melhor que vencer no campo e na aposta.
                    </p>
                    <ul class="mb-4 flex flex-col lg:mb-6">

                    </ul>
                    <ul class="flex gap-3 text-sm leading-normal">
                        <li>
                            <Link
                                :href="$page.props.auth.user ? dashboard() : register()"
                                class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                            >
                                {{
                                    $page.props.auth.user
                                        ? 'Acessar painel do Bolão'
                                        : 'Criar conta'
                                }}
                            </Link>
                        </li>
                    </ul>
                </div>
                <div
                    class="bg-white relative -mb-px aspect-[335/364] w-full shrink-0 overflow-hidden rounded-t-lg lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-[#1D0002] p-2"
                >
                    <img src="/logo.png" alt="Logo do Bolão 26" class="w-full h-full object-cover" />
                    <div
                        class="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    ></div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>

<style scoped>
.welcome-page {
    background-image: linear-gradient(
        133deg,
        rgba(42, 123, 155, 0.4) 0%,
        rgba(87, 199, 133, 0.4) 50%,
        rgba(237, 221, 83, 0.4) 100%
    );
}
</style>
