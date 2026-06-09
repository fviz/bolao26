<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, Send } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, read, readAll } from '@/routes/notifications';
import { store as storeBroadcast } from '@/routes/notifications/broadcast';
import type { AppNotification, NotificationPaginator } from '@/types';

type Props = {
    notifications: NotificationPaginator<AppNotification>;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Notificações',
                href: index(),
            },
        ],
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.user.is_admin);
const showBroadcastDialog = ref(false);

const formatDate = (value: string | null): string => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
};
</script>

<template>
    <Head title="Notificações" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Notificações
                </h1>
                <p class="text-sm text-muted-foreground">
                    Acompanhe lembretes, resultados e avisos do bolão.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="isAdmin"
                    variant="default"
                    @click="showBroadcastDialog = true"
                >
                    <Send />
                    Enviar notificação
                </Button>

                <Form
                    v-if="notifications.total > 0"
                    v-bind="readAll.form()"
                    :options="{ preserveScroll: true }"
                    v-slot="{ processing }"
                >
                    <Button
                        type="submit"
                        variant="outline"
                        :disabled="processing"
                    >
                        <CheckCheck />
                        Marcar todas como lidas
                    </Button>
                </Form>
            </div>
        </div>

        <div v-if="notifications.data.length" class="space-y-3">
            <Card
                v-for="notification in notifications.data"
                :key="notification.id"
                :class="[
                    'transition-colors',
                    !notification.readAt
                        ? 'border-primary/50 bg-primary/5'
                        : '',
                ]"
            >
                <CardHeader class="gap-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span
                                class="mt-1 flex size-8 items-center justify-center rounded-full bg-muted"
                            >
                                <Bell class="size-4" />
                            </span>
                            <div>
                                <CardTitle class="text-base">
                                    {{ notification.title }}
                                </CardTitle>
                                <CardDescription>
                                    {{ formatDate(notification.createdAt) }}
                                </CardDescription>
                            </div>
                        </div>
                        <span
                            v-if="!notification.readAt"
                            class="rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground"
                        >
                            Nova
                        </span>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm">
                        {{ notification.body }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-if="notification.url"
                            variant="outline"
                            as-child
                        >
                            <Link :href="notification.url"> Abrir </Link>
                        </Button>

                        <Form
                            v-if="!notification.readAt"
                            v-bind="read.form(notification.id)"
                            :options="{ preserveScroll: true }"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                variant="ghost"
                                :disabled="processing"
                            >
                                Marcar como lida
                            </Button>
                        </Form>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div
            v-else
            class="flex min-h-64 flex-col items-center justify-center rounded-xl border border-dashed p-8 text-center"
        >
            <Bell class="mb-3 size-8 text-muted-foreground" />
            <h2 class="text-lg font-medium">Nenhuma notificação ainda</h2>
            <p class="max-w-md text-sm text-muted-foreground">
                Quando houver lembretes de previsões, resultados ou avisos da
                Copa, eles aparecerão aqui.
            </p>
        </div>

        <nav
            v-if="notifications.links.length > 3"
            class="flex flex-wrap items-center gap-2"
            aria-label="Paginação de notificações"
        >
            <Button
                v-for="link in notifications.links"
                :key="link.label"
                :variant="link.active ? 'default' : 'outline'"
                :disabled="!link.url"
                size="sm"
                as-child
            >
                <Link v-if="link.url" :href="link.url" preserve-scroll>
                    <span v-html="link.label" />
                </Link>
                <span v-else v-html="link.label" />
            </Button>
        </nav>
    </div>

    <Dialog v-model:open="showBroadcastDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Enviar notificação</DialogTitle>
                <DialogDescription>
                    A mensagem será enviada para todos os usuários do bolão.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="storeBroadcast.form()"
                :options="{ preserveScroll: true }"
                reset-on-success
                class="space-y-4"
                @success="showBroadcastDialog = false"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="broadcast_title">Título</Label>
                    <Input
                        id="broadcast_title"
                        name="title"
                        placeholder="Ex.: Aviso importante"
                        maxlength="100"
                        required
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="broadcast_body">Mensagem</Label>
                    <Textarea
                        id="broadcast_body"
                        name="body"
                        placeholder="Escreva a mensagem da notificação…"
                        maxlength="500"
                        rows="4"
                        required
                    />
                    <InputError :message="errors.body" />
                </div>

                <div class="grid gap-2">
                    <Label for="broadcast_url">Link (opcional)</Label>
                    <Input
                        id="broadcast_url"
                        name="url"
                        type="url"
                        placeholder="https://"
                    />
                    <InputError :message="errors.url" />
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showBroadcastDialog = false"
                    >
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <Send />
                        Enviar
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
</template>
