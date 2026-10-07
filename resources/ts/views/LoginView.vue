<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { errorMessage, validationErrors } from '@/api/http';
import { useAuthStore } from '@/stores/auth';
import type { ValidationErrors } from '@/types';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const form = reactive({ email: '', password: '' });
const errors = ref<ValidationErrors>({});
const message = ref<string | null>(null);
const submitting = ref(false);

function useDemoAccount(): void {
    form.email = 'demo@stockflow.test';
    form.password = 'password';
}

async function submit(): Promise<void> {
    submitting.value = true;
    errors.value = {};
    message.value = null;

    try {
        await auth.login(form);
        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/';
        await router.replace(redirect);
    } catch (error) {
        errors.value = validationErrors(error);
        if (!Object.keys(errors.value).length) {
            message.value = errorMessage(error);
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-full items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 px-4">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex flex-col items-center text-center">
                <img src="/favicon.svg" alt="" class="size-12" />
                <h1 class="mt-4 text-2xl font-semibold text-white">StockFlow</h1>
                <p class="mt-1 text-sm text-slate-300">Inventory & purchasing for multi-branch stores</p>
            </div>

            <form class="card space-y-4 p-6" novalidate @submit.prevent="submit">
                <p v-if="message" class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ message }}</p>

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" v-model="form.email" type="email" autocomplete="username" class="input" required />
                    <p v-if="errors.email" class="mt-1 text-sm text-rose-600">{{ errors.email[0] }}</p>
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" v-model="form.password" type="password" autocomplete="current-password" class="input" required />
                    <p v-if="errors.password" class="mt-1 text-sm text-rose-600">{{ errors.password[0] }}</p>
                </div>

                <button type="submit" class="btn btn-primary w-full" :disabled="submitting">
                    {{ submitting ? 'Signing in…' : 'Sign in' }}
                </button>

                <button type="button" class="w-full text-center text-sm text-indigo-600 hover:underline" @click="useDemoAccount">
                    Use demo account (demo@stockflow.test)
                </button>
            </form>
        </div>
    </div>
</template>
