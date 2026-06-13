import { usePage } from '@inertiajs/vue3';
import { computed  } from 'vue';
import type {ComputedRef} from 'vue';

export function useHasPageProp(...propNames: string[]): ComputedRef<boolean> {
    const page = usePage();

    return computed(() => propNames.every((propName) => propName in page.props));
}
