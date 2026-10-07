import { ref, shallowRef, type Ref } from 'vue';
import { errorMessage } from '@/api/http';

/**
 * Wraps an async call with loading / error state for templates.
 */
export function useAsync<T, Args extends unknown[] = []>(fn: (...args: Args) => Promise<T>) {
    const data = shallowRef<T | null>(null) as Ref<T | null>;
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function run(...args: Args): Promise<T | null> {
        loading.value = true;
        error.value = null;

        try {
            data.value = await fn(...args);
            return data.value;
        } catch (e) {
            error.value = errorMessage(e);
            return null;
        } finally {
            loading.value = false;
        }
    }

    return { data, loading, error, run };
}
