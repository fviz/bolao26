import { usePage } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

export function useHasPageProp(propName: string): ComputedRef<boolean> {
    const page = usePage();

    return computed(() => propName in page.props);
}
