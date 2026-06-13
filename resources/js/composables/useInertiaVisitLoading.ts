import { router } from '@inertiajs/vue3';
import { readonly, ref, type DeepReadonly, type Ref } from 'vue';

const isNavigating = ref(false);
let listenersRegistered = false;

function isPrefetchVisit(visit: { prefetch: boolean }): boolean {
    return visit.prefetch;
}

function registerListeners(): void {
    if (listenersRegistered) {
        return;
    }

    listenersRegistered = true;

    router.on('start', (event) => {
        if (isPrefetchVisit(event.detail.visit)) {
            return;
        }

        isNavigating.value = true;
    });

    router.on('finish', (event) => {
        if (isPrefetchVisit(event.detail.visit)) {
            return;
        }

        isNavigating.value = false;
    });

    router.on('cancel', () => {
        isNavigating.value = false;
    });
}

type UseInertiaVisitLoadingReturn = {
    isNavigating: DeepReadonly<Ref<boolean>>;
};

export function useInertiaVisitLoading(): UseInertiaVisitLoadingReturn {
    registerListeners();

    return {
        isNavigating: readonly(isNavigating),
    };
}
