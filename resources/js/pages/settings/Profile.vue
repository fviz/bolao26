<script setup lang="ts">
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useHasPageProp } from '@/composables/useHasPageProp';
import { useInitials } from '@/composables/useInitials';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail?: boolean;
    status?: string;
};

defineProps<Props>();

const isReady = useHasPageProp('mustVerifyEmail');

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ajustes de perfil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const { getInitials } = useInitials();

const avatarPreviewUrl = ref<string | null>(null);

const avatarForm = useForm<{ avatar: File | null }>({
    avatar: null,
});

const displayAvatarUrl = computed(
    () => avatarPreviewUrl.value ?? user.value.avatar ?? null,
);

const onAvatarFileChange = (event: Event): void => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;

    avatarForm.avatar = file;

    if (avatarPreviewUrl.value) {
        URL.revokeObjectURL(avatarPreviewUrl.value);
    }

    avatarPreviewUrl.value = file ? URL.createObjectURL(file) : null;
};

const submitAvatar = (): void => {
    if (!avatarForm.avatar) {
        return;
    }

    avatarForm.post(ProfileController.storeAvatar.url(), {
        preserveScroll: true,
        onSuccess: () => {
            avatarForm.reset('avatar');

            if (avatarPreviewUrl.value) {
                URL.revokeObjectURL(avatarPreviewUrl.value);
                avatarPreviewUrl.value = null;
            }
        },
    });
};
</script>

<template>
    <Head title="Ajustes de perfil" />

    <template v-if="isReady">
        <h1 class="sr-only">Ajustes de perfil</h1>

        <div class="flex flex-col space-y-6">
            <Heading
                variant="small"
                title="Foto de perfil"
                description="Envie uma imagem (máximo 5 MB). Formatos: JPEG, PNG ou WebP."
            />

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div
                    class="size-20 shrink-0 overflow-hidden rounded-full border bg-muted"
                    data-test="profile-avatar-preview"
                >
                    <div
                        v-if="displayAvatarUrl"
                        class="size-full bg-cover bg-center"
                        :style="{ backgroundImage: `url(${displayAvatarUrl})` }"
                        role="img"
                        :aria-label="`Foto de perfil de ${user.name}`"
                    />
                    <div
                        v-else
                        class="flex size-full items-center justify-center text-lg font-medium text-muted-foreground"
                    >
                        {{ getInitials(user.name) }}
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <form
                        class="flex flex-wrap items-center gap-3"
                        @submit.prevent="submitAvatar"
                    >
                        <div class="grid gap-2">
                            <Label for="avatar" class="sr-only"
                                >Selecionar foto de perfil</Label
                            >
                            <input
                                id="avatar"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="max-w-xs rounded-md border border-input bg-transparent px-3 py-1 text-sm file:mr-3 file:border-0 file:bg-transparent file:text-sm file:font-medium"
                                @change="onAvatarFileChange"
                            />
                            <InputError :message="avatarForm.errors.avatar" />
                        </div>
                        <Button
                            type="submit"
                            :disabled="
                                avatarForm.processing || !avatarForm.avatar
                            "
                            data-test="upload-avatar-button"
                        >
                            Enviar foto
                        </Button>
                    </form>

                    <Form
                        v-if="user.avatar"
                        v-bind="ProfileController.destroyAvatar.form()"
                        :options="{ preserveScroll: true }"
                        v-slot="{ processing }"
                    >
                        <Button
                            type="submit"
                            variant="outline"
                            :disabled="processing"
                            data-test="remove-avatar-button"
                        >
                            Remover foto
                        </Button>
                    </Form>
                </div>
            </div>

            <Heading
                variant="small"
                title="Informações do perfil"
                description="Atualize seu nome e endereço de email"
            />

            <Form
                v-bind="ProfileController.update.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="name">Nome</Label>
                    <Input
                        id="name"
                        class="mt-1 block w-full"
                        name="name"
                        :default-value="user.name"
                        required
                        autocomplete="name"
                        placeholder="Nome completo"
                    />
                    <InputError class="mt-2" :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Endereço de email</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        name="email"
                        :default-value="user.email"
                        required
                        autocomplete="username"
                        placeholder="Endereço de email"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Seu endereço de email não está verificado.
                        <Link
                            :href="send()"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Clique aqui para reenviar o email de verificação.
                        </Link>
                    </p>

                    <div
                        v-if="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600"
                    >
                        Um novo link de verificação foi enviado para seu
                        endereço de email.
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        :disabled="processing"
                        data-test="update-profile-button"
                        >Salvar</Button
                    >
                </div>
            </Form>
        </div>

        <DeleteUser />
    </template>
</template>
