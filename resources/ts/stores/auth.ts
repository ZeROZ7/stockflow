import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { authApi, type Credentials } from '@/api/auth';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const loaded = ref(false);

    const isAuthenticated = computed(() => user.value !== null);
    const isManager = computed(() => user.value?.is_manager ?? false);

    /** Ask Laravel who is logged in (based on the session cookie). */
    async function fetchUser(): Promise<void> {
        try {
            user.value = await authApi.me();
        } catch {
            user.value = null;
        } finally {
            loaded.value = true;
        }
    }

    async function ensureLoaded(): Promise<void> {
        if (!loaded.value) {
            await fetchUser();
        }
    }

    async function login(credentials: Credentials): Promise<void> {
        await authApi.csrfCookie();
        user.value = await authApi.login(credentials);
        loaded.value = true;
    }

    async function logout(): Promise<void> {
        try {
            await authApi.logout();
        } finally {
            user.value = null;
        }
    }

    function clear(): void {
        user.value = null;
    }

    return { user, loaded, isAuthenticated, isManager, fetchUser, ensureLoaded, login, logout, clear };
});
