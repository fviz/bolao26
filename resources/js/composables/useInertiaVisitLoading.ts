import { router } from '@inertiajs/vue3';
import { readonly, ref, type DeepReadonly, type Ref } from 'vue';

const isNavigating = ref(false);
let listenersRegistered = false;

function registerListeners(): void {
    if (listenersRegistered) {
        return;
    }

    listenersRegistered = true;

    router.on('start', () => {
        isNavigating.value = true;
    });

    router.on('finish', () => {
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
