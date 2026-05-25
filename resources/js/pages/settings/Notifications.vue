<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { BellRing } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useWebPush } from '@/composables/useWebPush';
import { edit, update } from '@/routes/notifications/settings';
import type { NotificationPreferences } from '@/types';

type Props = {
    preferences: NotificationPreferences;
    gameReminderMinuteOptions: number[];
    browserPushPublicKey: string | null;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ajustes de notificações',
                href: edit(),
            },
        ],
    },
});

const systemTimezone =
    Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
const pushError = ref<string | null>(null);
const pushProcessing = ref(false);
const testProcessing = ref(false);
const testSent = ref(false);

const form = useForm({
    missing_prediction_reminders_enabled:
        props.preferences.missingPredictionRemindersEnabled,
    game_result_notifications_enabled:
        props.preferences.gameResultNotificationsEnabled,
    daily_summary_enabled: props.preferences.dailySummaryEnabled,
    tournament_deadline_enabled: props.preferences.tournamentDeadlineEnabled,
    browser_notifications_enabled:
        props.preferences.browserNotificationsEnabled,
    game_reminder_minutes: props.preferences.gameReminderMinutes,
    daily_summary_time: props.preferences.dailySummaryTime,
    daily_summary_timezone:
        props.preferences.dailySummaryTimezone || systemTimezone,
});

const {
    isSupported,
    permission,
    isSubscribed,
    refreshSubscription,
    sendTestNotification,
    subscribe,
    unsubscribe,
} = useWebPush();

const browserStatus = computed(() => {
    if (!isSupported.value) {
        return 'Este navegador não suporta notificações push.';
    }

    if (!props.browserPushPublicKey) {
        return 'As chaves VAPID precisam ser configuradas no servidor.';
    }

    if (permission.value === 'denied') {
        return 'A permissão de notificações foi bloqueada no navegador.';
    }

    return isSubscribed.value
        ? 'Notificações do navegador habilitadas neste dispositivo.'
        : 'Habilite para receber notificações mesmo com o app fechado.';
});

const reminderLabel = (minutes: number): string => {
    if (minutes === 30) {
        return '30 minutos';
    }

    if (minutes === 60) {
        return '1 hora';
    }

    return `${minutes / 60} horas`;
};

const submit = (): void => {
    form.patch(update.url(), {
        preserveScroll: true,
        preserveState: false,
    });
};

const enableBrowserNotifications = async (): Promise<void> => {
    pushProcessing.value = true;
    pushError.value = null;

    try {
        await subscribe();
        form.browser_notifications_enabled = true;
        testSent.value = false;
    } catch (error) {
        pushError.value =
            error instanceof Error
                ? error.message
                : 'Não foi possível habilitar notificações do navegador.';
    } finally {
        pushProcessing.value = false;
    }
};

const disableBrowserNotifications = async (): Promise<void> => {
    pushProcessing.value = true;
    pushError.value = null;

    try {
        await unsubscribe();
        form.browser_notifications_enabled = false;
        testSent.value = false;
    } catch (error) {
        pushError.value =
            error instanceof Error
                ? error.message
                : 'Não foi possível desabilitar notificações do navegador.';
    } finally {
        pushProcessing.value = false;
    }
};

const testBrowserNotification = async (): Promise<void> => {
    testProcessing.value = true;
    testSent.value = false;
    pushError.value = null;

    try {
        await sendTestNotification();
        testSent.value = true;
    } catch (error) {
        pushError.value =
            error instanceof Error
                ? error.message
                : 'Não foi possível enviar a notificação de teste.';
    } finally {
        testProcessing.value = false;
    }
};

onMounted(() => {
    void refreshSubscription();
});
</script>

<template>
    <Head title="Ajustes de notificações" />

    <h1 class="sr-only">Ajustes de notificações</h1>

    <form class="space-y-8" @submit.prevent="submit">
        <section class="space-y-6">
            <Heading
                variant="small"
                title="Notificações do bolão"
                description="Escolha quais lembretes e resultados você quer receber."
            />

            <div class="space-y-4">
                <label class="flex items-start gap-3">
                    <Checkbox
                        v-model="form.missing_prediction_reminders_enabled"
                        class="mt-1"
                    />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium">
                            Lembrete antes do jogo
                        </span>
                        <span class="block text-sm text-muted-foreground">
                            Receba um aviso quando um jogo estiver perto de
                            começar e você ainda não tiver feito previsão.
                        </span>
                        <InputError
                            :message="
                                form.errors.missing_prediction_reminders_enabled
                            "
                        />
                    </span>
                </label>

                <div class="grid gap-2 pl-7">
                    <Label for="game_reminder_minutes">
                        Quanto tempo antes do jogo?
                    </Label>
                    <select
                        id="game_reminder_minutes"
                        v-model.number="form.game_reminder_minutes"
                        name="game_reminder_minutes"
                        class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!form.missing_prediction_reminders_enabled"
                    >
                        <option
                            v-for="minutes in gameReminderMinuteOptions"
                            :key="minutes"
                            :value="minutes"
                        >
                            {{ reminderLabel(minutes) }}
                        </option>
                    </select>
                    <InputError :message="form.errors.game_reminder_minutes" />
                </div>

                <label class="flex items-start gap-3">
                    <Checkbox
                        v-model="form.game_result_notifications_enabled"
                        class="mt-1"
                    />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium">
                            Resultado das previsões
                        </span>
                        <span class="block text-sm text-muted-foreground">
                            Receba os pontos garantidos quando um jogo terminar.
                        </span>
                        <InputError
                            :message="
                                form.errors.game_result_notifications_enabled
                            "
                        />
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <Checkbox
                        v-model="form.daily_summary_enabled"
                        class="mt-1"
                    />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium">
                            Resumo diário
                        </span>
                        <span class="block text-sm text-muted-foreground">
                            Receba um lembrete diário quando ainda houver
                            previsões pendentes para os jogos do dia.
                        </span>
                        <InputError
                            :message="form.errors.daily_summary_enabled"
                        />
                    </span>
                </label>

                <div class="grid gap-2 pl-7">
                    <Label for="daily_summary_time">
                        Horário no seu fuso local
                    </Label>
                    <Input
                        id="daily_summary_time"
                        v-model="form.daily_summary_time"
                        name="daily_summary_time"
                        type="time"
                        :disabled="!form.daily_summary_enabled"
                    />
                    <p class="text-xs text-muted-foreground">
                        Fuso detectado: {{ form.daily_summary_timezone }}.
                    </p>
                    <InputError :message="form.errors.daily_summary_time" />
                    <InputError :message="form.errors.daily_summary_timezone" />
                </div>

                <label class="flex items-start gap-3">
                    <Checkbox
                        v-model="form.tournament_deadline_enabled"
                        class="mt-1"
                    />
                    <span class="space-y-1">
                        <span class="block text-sm font-medium">
                            Previsões de campeão e artilheiro
                        </span>
                        <span class="block text-sm text-muted-foreground">
                            Receba um aviso perto do início da Copa caso essas
                            previsões ainda estejam pendentes.
                        </span>
                        <InputError
                            :message="form.errors.tournament_deadline_enabled"
                        />
                    </span>
                </label>
            </div>
        </section>

        <section class="space-y-4">
            <Heading
                variant="small"
                title="Notificações do navegador"
                description="Use o sistema de notificações do seu dispositivo quando disponível."
            />

            <div class="rounded-xl border p-4">
                <div class="flex items-start gap-3">
                    <BellRing class="mt-1 size-5 text-muted-foreground" />
                    <div class="flex-1 space-y-3">
                        <div>
                            <p class="text-sm font-medium">Este dispositivo</p>
                            <p class="text-sm text-muted-foreground">
                                {{ browserStatus }}
                            </p>
                            <p class="mt-2 text-xs text-muted-foreground">
                                Em celulares, pode ser necessário instalar o app
                                na tela inicial e acessar por HTTPS.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-if="!isSubscribed"
                                type="button"
                                variant="outline"
                                :disabled="
                                    pushProcessing ||
                                    !isSupported ||
                                    !browserPushPublicKey ||
                                    permission === 'denied'
                                "
                                @click="enableBrowserNotifications"
                            >
                                Habilitar neste dispositivo
                            </Button>
                            <Button
                                v-else
                                type="button"
                                variant="outline"
                                :disabled="pushProcessing || testProcessing"
                                @click="disableBrowserNotifications"
                            >
                                Desabilitar neste dispositivo
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                :disabled="
                                    pushProcessing ||
                                    testProcessing ||
                                    !isSubscribed
                                "
                                @click="testBrowserNotification"
                            >
                                {{
                                    testProcessing
                                        ? 'Enviando teste...'
                                        : 'Testar notificação'
                                }}
                            </Button>
                        </div>

                        <p
                            v-if="testSent"
                            class="text-sm font-medium text-green-600"
                        >
                            Notificação de teste enviada para este navegador.
                        </p>
                        <p
                            v-if="pushError"
                            class="text-sm font-medium text-destructive"
                        >
                            {{ pushError }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex items-center gap-4">
            <Button :disabled="form.processing">Salvar preferências</Button>
            <p
                v-if="form.recentlySuccessful"
                class="text-sm font-medium text-green-600"
            >
                Preferências salvas.
            </p>
        </div>
    </form>
</template>
