<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { branchesApi, type BranchInput } from '@/api/catalog';
import { errorMessage, validationErrors } from '@/api/http';
import AlertMessage from '@/components/AlertMessage.vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import { useAsync } from '@/composables/useAsync';
import { formatNumber } from '@/composables/useFormat';
import { useAuthStore } from '@/stores/auth';
import type { Branch, ValidationErrors } from '@/types';

const auth = useAuthStore();
const { data: branches, error, run: loadBranches } = useAsync(branchesApi.list);
const { data: stock, loading: loadingStock, run: loadStock } = useAsync(branchesApi.stock);

const selected = ref<Branch | null>(null);
const lowCount = computed(() => stock.value?.filter((row) => row.is_low).length ?? 0);

async function select(branch: Branch): Promise<void> {
    selected.value = branch;
    await loadStock(branch.id);
}

onMounted(async () => {
    const list = await loadBranches();
    if (list?.length) await select(list[0]);
});

const showForm = ref(false);
const form = reactive<BranchInput>({ code: '', name: '', city: '', address: '' });
const errors = ref<ValidationErrors>({});
const formError = ref<string | null>(null);

async function createBranch(): Promise<void> {
    errors.value = {};
    formError.value = null;

    try {
        const branch = await branchesApi.create({ ...form });
        Object.assign(form, { code: '', name: '', city: '', address: '' });
        showForm.value = false;
        await loadBranches();
        await select(branch);
    } catch (e) {
        errors.value = validationErrors(e);
        formError.value = Object.keys(errors.value).length ? null : errorMessage(e);
    }
}
</script>

<template>
    <PageHeader title="Branches & stock" subtitle="Current stock per branch. Rows in red are below the product minimum.">
        <button v-if="auth.isManager" type="button" class="btn btn-secondary" @click="showForm = !showForm">
            <AppIcon name="plus" class="size-4" /> Add branch
        </button>
    </PageHeader>

    <AlertMessage :message="error" />

    <form v-if="showForm" class="card mb-6 grid grid-cols-1 gap-4 p-5 sm:grid-cols-4" @submit.prevent="createBranch">
        <div class="sm:col-span-4"><AlertMessage :message="formError" /></div>
        <div v-for="field in ['code', 'name', 'city', 'address'] as const" :key="field">
            <label class="label capitalize" :for="field">{{ field }}</label>
            <input :id="field" v-model="form[field]" class="input" />
            <p v-if="errors[field]" class="mt-1 text-xs text-rose-600">{{ errors[field][0] }}</p>
        </div>
        <div class="flex justify-end sm:col-span-4">
            <button type="submit" class="btn btn-primary">Create branch</button>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <ul class="space-y-2">
            <li v-for="branch in branches ?? []" :key="branch.id">
                <button
                    type="button"
                    class="card w-full px-4 py-3 text-left transition hover:ring-indigo-300"
                    :class="selected?.id === branch.id ? 'ring-2 ring-indigo-600!' : ''"
                    @click="select(branch)"
                >
                    <p class="font-medium text-slate-900">{{ branch.name }}</p>
                    <p class="text-xs text-slate-500">{{ branch.code }} · {{ branch.city }}</p>
                </button>
            </li>
        </ul>

        <section v-if="selected" class="card overflow-hidden lg:col-span-3">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">{{ selected.name }}</h2>
                    <p class="text-xs text-slate-500">{{ selected.address }}</p>
                </div>
                <span v-if="lowCount" class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">
                    {{ lowCount }} below minimum
                </span>
            </header>
            <table class="table">
                <thead class="bg-slate-50">
                    <tr>
                        <th>Product</th>
                        <th>On hand</th>
                        <th>Minimum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" :class="{ 'opacity-50': loadingStock }">
                    <tr v-for="row in stock ?? []" :key="row.product_id" :class="row.is_low ? 'bg-rose-50/60' : ''">
                        <td>
                            <span class="font-medium text-slate-900">{{ row.name }}</span>
                            <span class="ml-1 text-xs text-slate-400">{{ row.sku }}</span>
                        </td>
                        <td :class="row.is_low ? 'font-semibold text-rose-700' : ''">{{ formatNumber(row.quantity) }} {{ row.unit }}</td>
                        <td>{{ formatNumber(row.min_stock) }} {{ row.unit }}</td>
                        <td class="text-right">
                            <RouterLink
                                v-if="row.is_low"
                                :to="{ name: 'purchase-orders.create', query: { branch: selected.id, product: row.product_id, quantity: row.min_stock - row.quantity } }"
                                class="text-sm font-medium text-indigo-600 hover:underline"
                            >
                                Reorder
                            </RouterLink>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
</template>
