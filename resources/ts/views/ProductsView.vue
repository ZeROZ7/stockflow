<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue';
import { productsApi, type ProductInput } from '@/api/catalog';
import { errorMessage, validationErrors } from '@/api/http';
import AlertMessage from '@/components/AlertMessage.vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import { useAsync } from '@/composables/useAsync';
import { formatNumber } from '@/composables/useFormat';
import { useAuthStore } from '@/stores/auth';
import type { Product, ValidationErrors } from '@/types';

const auth = useAuthStore();
const units = ['unit', 'kg', 'g', 'l', 'ml', 'box', 'pack'];

const search = ref('');
const { data: products, loading, error, run: load } = useAsync(productsApi.list);

onMounted(() => load());

// Small debounce so we do not hit the API on every keystroke.
let timer: ReturnType<typeof setTimeout> | undefined;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => load(value), 250);
});

const editing = ref<Product | null>(null);
const showForm = ref(false);
const form = reactive<ProductInput>({ sku: '', name: '', unit: 'unit', min_stock: 0 });
const errors = ref<ValidationErrors>({});
const formError = ref<string | null>(null);
const saving = ref(false);

function openForm(product: Product | null = null): void {
    editing.value = product;
    Object.assign(form, product ?? { sku: '', name: '', unit: 'unit', min_stock: 0 });
    errors.value = {};
    formError.value = null;
    showForm.value = true;
}

async function save(): Promise<void> {
    saving.value = true;
    errors.value = {};
    formError.value = null;

    try {
        if (editing.value) {
            await productsApi.update(editing.value.id, { ...form });
        } else {
            await productsApi.create({ ...form });
        }
        showForm.value = false;
        await load(search.value);
    } catch (e) {
        errors.value = validationErrors(e);
        formError.value = Object.keys(errors.value).length ? null : errorMessage(e);
    } finally {
        saving.value = false;
    }
}

async function remove(product: Product): Promise<void> {
    if (!confirm(`Delete ${product.name}?`)) return;

    try {
        await productsApi.remove(product.id);
        await load(search.value);
    } catch (e) {
        alert(errorMessage(e));
    }
}
</script>

<template>
    <PageHeader title="Products" subtitle="Catalog shared by every branch, with the minimum stock per branch.">
        <input v-model="search" type="search" placeholder="Search name or SKU…" class="input w-64" />
        <button v-if="auth.isManager" type="button" class="btn btn-primary" @click="openForm()">
            <AppIcon name="plus" class="size-4" /> Add product
        </button>
    </PageHeader>

    <AlertMessage :message="error" />

    <form v-if="showForm" class="card mb-6 grid grid-cols-1 gap-4 p-5 sm:grid-cols-5" @submit.prevent="save">
        <div class="sm:col-span-5"><AlertMessage :message="formError" /></div>
        <div>
            <label class="label" for="sku">SKU</label>
            <input id="sku" v-model="form.sku" class="input uppercase" />
            <p v-if="errors.sku" class="mt-1 text-xs text-rose-600">{{ errors.sku[0] }}</p>
        </div>
        <div class="sm:col-span-2">
            <label class="label" for="name">Name</label>
            <input id="name" v-model="form.name" class="input" />
            <p v-if="errors.name" class="mt-1 text-xs text-rose-600">{{ errors.name[0] }}</p>
        </div>
        <div>
            <label class="label" for="unit">Unit</label>
            <select id="unit" v-model="form.unit" class="input">
                <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
            </select>
        </div>
        <div>
            <label class="label" for="min_stock">Min. stock</label>
            <input id="min_stock" v-model.number="form.min_stock" type="number" min="0" class="input" />
            <p v-if="errors.min_stock" class="mt-1 text-xs text-rose-600">{{ errors.min_stock[0] }}</p>
        </div>
        <div class="flex justify-end gap-2 sm:col-span-5">
            <button type="button" class="btn btn-secondary" @click="showForm = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">{{ editing ? 'Save changes' : 'Create product' }}</button>
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table">
            <thead class="bg-slate-50">
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Unit</th>
                    <th>Min. stock</th>
                    <th v-if="auth.isManager"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-for="product in products ?? []" :key="product.id">
                    <td class="font-mono text-xs text-slate-500">{{ product.sku }}</td>
                    <td class="font-medium text-slate-900">{{ product.name }}</td>
                    <td>{{ product.unit }}</td>
                    <td>{{ formatNumber(product.min_stock) }}</td>
                    <td v-if="auth.isManager" class="space-x-3 text-right">
                        <button type="button" class="text-sm font-medium text-indigo-600 hover:underline" @click="openForm(product)">Edit</button>
                        <button type="button" class="text-sm font-medium text-rose-600 hover:underline" @click="remove(product)">Delete</button>
                    </td>
                </tr>
                <tr v-if="!loading && !products?.length">
                    <td colspan="5" class="py-8 text-center text-slate-500">No products found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
