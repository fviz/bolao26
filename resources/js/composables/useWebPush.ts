import { ref } from 'vue';
import {
    destroy as destroyPushSubscription,
    store as storePushSubscription,
    test as testPushSubscription,
    vapidKey,
} from '@/routes/push-subscriptions';

type VapidKeyResponse = {
    publicKey: string | null;
};

type JsonErrorResponse = {
    message?: string;
};

type PushSubscriptionPayload = PushSubscriptionJSON & {
    contentEncoding: string;
};

const isSupported = ref(
    typeof window !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window,
);

const permission = ref<NotificationPermission>(
    isSupported.value ? Notification.permission : 'denied',
);

const isSubscribed = ref(false);
const subscriptionEndpoint = ref<string | null>(null);

function csrfToken(): string {
    return (
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

function urlBase64ToArrayBuffer(value: string): ArrayBuffer {
    const padding = '='.repeat((4 - (value.length % 4)) % 4);
    const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const output = new Uint8Array(rawData.length);

    for (let index = 0; index < rawData.length; index++) {
        output[index] = rawData.charCodeAt(index);
    }

    return output.buffer as ArrayBuffer;
}

async function postJson(
    url: string,
    payload: unknown,
    method = 'POST',
    fallbackMessage = 'Não foi possível atualizar a inscrição de notificações.',
): Promise<void> {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        const data = (await response
            .json()
            .catch(() => null)) as JsonErrorResponse | null;

        throw new Error(data?.message ?? fallbackMessage);
    }
}

async function publicKey(): Promise<string> {
    const response = await fetch(vapidKey.url(), {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
        },
    });

    if (!response.ok) {
        throw new Error(
            'Não foi possível carregar a chave pública de notificações.',
        );
    }

    const data = (await response.json()) as VapidKeyResponse;

    if (!data.publicKey) {
        throw new Error('As chaves VAPID ainda não foram configuradas.');
    }

    return data.publicKey;
}

async function registration(): Promise<ServiceWorkerRegistration> {
    await navigator.serviceWorker.register('/sw.js');

    return navigator.serviceWorker.ready;
}

export function useWebPush() {
    const refreshSubscription = async (): Promise<void> => {
        if (!isSupported.value) {
            return;
        }

        const serviceWorkerRegistration = await registration();
        const subscription =
            await serviceWorkerRegistration.pushManager.getSubscription();

        permission.value = Notification.permission;
        isSubscribed.value = subscription !== null;
        subscriptionEndpoint.value = subscription?.endpoint ?? null;
    };

    const subscribe = async (): Promise<void> => {
        if (!isSupported.value) {
            throw new Error('Este navegador não suporta notificações push.');
        }

        const notificationPermission = await Notification.requestPermission();
        permission.value = notificationPermission;

        if (notificationPermission !== 'granted') {
            throw new Error('Permissão de notificações não concedida.');
        }

        const serviceWorkerRegistration = await registration();
        const existingSubscription =
            await serviceWorkerRegistration.pushManager.getSubscription();
        const subscription =
            existingSubscription ??
            (await serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToArrayBuffer(await publicKey()),
            }));

        const payload = subscription.toJSON() as PushSubscriptionPayload;
        payload.contentEncoding =
            PushManager.supportedContentEncodings?.includes('aes128gcm')
                ? 'aes128gcm'
                : 'aesgcm';

        await postJson(storePushSubscription.url(), payload);
        isSubscribed.value = true;
        subscriptionEndpoint.value = subscription.endpoint;
    };

    const sendTestNotification = async (): Promise<void> => {
        if (!isSupported.value) {
            throw new Error('Este navegador não suporta notificações push.');
        }

        const serviceWorkerRegistration = await registration();
        const subscription =
            await serviceWorkerRegistration.pushManager.getSubscription();

        permission.value = Notification.permission;
        isSubscribed.value = subscription !== null;
        subscriptionEndpoint.value = subscription?.endpoint ?? null;

        if (!subscription) {
            throw new Error(
                'Habilite as notificações neste dispositivo antes de testar.',
            );
        }

        await postJson(
            testPushSubscription.url(),
            { endpoint: subscription.endpoint },
            'POST',
            'Não foi possível enviar a notificação de teste.',
        );
    };

    const unsubscribe = async (): Promise<void> => {
        if (!isSupported.value) {
            return;
        }

        const serviceWorkerRegistration = await registration();
        const subscription =
            await serviceWorkerRegistration.pushManager.getSubscription();

        if (!subscription) {
            isSubscribed.value = false;

            return;
        }

        await postJson(
            destroyPushSubscription.url(),
            { endpoint: subscription.endpoint },
            'DELETE',
        );

        await subscription.unsubscribe();
        isSubscribed.value = false;
        subscriptionEndpoint.value = null;
    };

    return {
        isSupported,
        permission,
        isSubscribed,
        refreshSubscription,
        sendTestNotification,
        subscribe,
        unsubscribe,
    };
}
