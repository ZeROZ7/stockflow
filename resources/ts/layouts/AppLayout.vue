<script setup lang="ts">
import { useRouter } from 'vue-router';
import AppIcon, { type IconName } from '@/components/AppIcon.vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

const navigation: { name: string; to: string; icon: IconName }[] = [
    { name: 'Dashboard', to: '/', icon: 'home' },
    { name: 'Purchase orders', to: '/purchase-orders', icon: 'document' },
    { name: 'Branches & stock', to: '/branches', icon: 'pin' },
    { name: 'Products', to: '/products', icon: 'cube' },
];

async function logout(): Promise<void> {
    await auth.logout();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div class="flex min-h-full">
        <aside class="fixed inset-y-0 flex w-64 flex-col bg-slate-900 px-4 py-6">
            <RouterLink to="/" class="mb-8 flex items-center gap-2 px-2">
                <img src="/favicon.svg" alt="" class="size-8" />
                <span class="text-lg font-semibold text-white">StockFlow</span>
            </RouterLink>

            <nav class="flex flex-1 flex-col gap-1">
                <RouterLink
                    v-for="item in navigation"
                    :key="item.to"
                    :to="item.to"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                    :exact-active-class="item.to === '/' ? 'bg-slate-800 text-white' : ''"
                    :active-class="item.to === '/' ? '' : 'bg-slate-800 text-white'"
                >
                    <AppIcon :name="item.icon" class="size-5" />
                    {{ item.name }}
                </RouterLink>
            </nav>

            <div class="border-t border-slate-800 pt-4">
                <div class="px-2">
                    <p class="text-sm font-medium text-white">{{ auth.user?.name }}</p>
                    <p class="text-xs text-slate-400">{{ auth.user?.email }} · {{ auth.isManager ? 'Manager' : 'Staff' }}</p>
                </div>
                <button
                    type="button"
                    class="mt-3 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white"
                    @click="logout"
                >
                    <AppIcon name="logout" class="size-5" />
                    Sign out
                </button>
            </div>
        </aside>

        <main class="ml-64 flex-1 px-8 py-8">
            <div class="mx-auto max-w-6xl">
                <slot />
            </div>
        </main>
    </div>
</template>
