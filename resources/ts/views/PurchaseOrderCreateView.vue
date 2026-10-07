<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { branchesApi, productsApi } from '@/api/catalog';
import { errorMessage, validationErrors } from '@/api/http';
import { purchaseOrdersApi, type PurchaseOrderInput } from '@/api/purchaseOrders';
import AlertMessage from '@/components/AlertMessage.vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import { formatCLP } from '@/composables/useFormat';
import type { Branch, Product, ValidationErrors } from '@/types';

const route = useRoute();
const router = useRouter();

const branches = ref<Branch[]>([]);
const products = ref<Product[]>([]);
const errors = ref<ValidationErrors>({});
const message = ref<string | null>(null);
const saving = ref(false);

const form = reactive<PurchaseOrderInput>({
    branch_id: null,
    supplier: '',
    notes: '',
    lines: [],
});

const total = computed(() => form.lines.reduce((sum, line) => sum + line.quantity * line.unit_cost, 0));
const productById = computed(() => new Map(products.value.map((product) => [product.id, product])));

function addLine(productId: number | null = null, quantity = 1): void {
    form.lines.push({ product_id: productId, quantity, unit_cost: 0 });
}

function removeLine(index: number): void {
    form.lines.splice(index, 1);
}

function fieldError(key: string): string | undefined {
    return errors.value[key]?.[0];
}

onMounted(async () => {
    [branches.value, products.value] = await Promise.all([branchesApi.list(), productsApi.list()]);

    // Prefill when coming from a "Reorder" link on a low-stock alert.
    const { branch, product, quantity } = route.query;
    form.branch_id = branch ? Number(branch) : (branches.value[0]?.id ?? null);
    addLine(product ? Number(product) : null, quantity ? Math.max(1, Number(quantity)) : 1);
});

async function submit(): Promise<void> {
    saving.value = true;
    errors.value = {};
    message.value = null;

    try {
        const order = await purchaseOrdersApi.create(form);
        await router.push({ name: 'purchase-orders.show', params: { id: order.id } });
    } catch (e) {
        errors.value = validationErrors(e);
        message.value = errorMessage(e, 'Could not create the purchase order.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <PageHeader title="New purchase order" subtitle="It starts as a draft; a manager approves it before goods are received.">
        <RouterLink to="/purchase-orders" class="btn btn-secondary">
            <AppIcon name="arrowLeft" class="size-4" /> Back
        </RouterLink>
    </PageHeader>

    <form class="space-y-6" @submit.prevent="submit">
        <AlertMessage :message="message" />

        <section class="card grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
            <div>
                <label class="label" for="branch">Branch</label>
                <select id="branch" v-model="form.branch_id" class="input">
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }} ({{ branch.code }})</option>
                </select>
                <p v-if="fieldError('branch_id')" class="mt-1 text-xs text-rose-600">{{ fieldError('branch_id') }}</p>
            </div>
            <div>
                <label class="label" for="supplier">Supplier</label>
                <input id="supplier" v-model="form.supplier" class="input" placeholder="e.g. Agrícola Los Andes" />
                <p v-if="fieldError('supplier')" class="mt-1 text-xs text-rose-600">{{ fieldError('supplier') }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="notes">Notes</label>
                <textarea id="notes" v-model="form.notes" rows="2" class="input" placeholder="Delivery instructions (optional)" />
            </div>
        </section>

        <section class="card overflow-hidden">
            <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Lines</h2>
                <button type="button" class="btn btn-secondary" @click="addLine()">
                    <AppIcon name="plus" class="size-4" /> Add line
                </button>
            </header>
            <table class="table">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-1/2">Product</th>
                        <th>Quantity</th>
                        <th>Unit cost (CLP)</th>
                        <th class="text-right!">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="(line, index) in form.lines" :key="index" class="align-top">
                        <td>
                            <select v-model="line.product_id" class="input">
                                <option :value="null" disabled>Select a product…</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} · {{ product.sku }}</option>
                            </select>
                            <p v-if="fieldError(`lines.${index}.product_id`)" class="mt-1 text-xs text-rose-600">{{ fieldError(`lines.${index}.product_id`) }}</p>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <input v-model.number="line.quantity" type="number" min="1" class="input w-24" />
                                <span class="text-xs text-slate-500">{{ line.product_id ? productById.get(line.product_id)?.unit : '' }}</span>
                            </div>
                            <p v-if="fieldError(`lines.${index}.quantity`)" class="mt-1 text-xs text-rose-600">{{ fieldError(`lines.${index}.quantity`) }}</p>
                        </td>
                        <td>
                            <input v-model.number="line.unit_cost" type="number" min="0" step="1" class="input w-32" />
                            <p v-if="fieldError(`lines.${index}.unit_cost`)" class="mt-1 text-xs text-rose-600">{{ fieldError(`lines.${index}.unit_cost`) }}</p>
                        </td>
                        <td class="pt-5 text-right font-medium text-slate-900">{{ formatCLP(line.quantity * line.unit_cost) }}</td>
                        <td class="text-right">
                            <button type="button" class="mt-1.5 text-slate-400 hover:text-rose-600" aria-label="Remove line" @click="removeLine(index)">
                                <AppIcon name="x" class="size-5" />
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!form.lines.length">
                        <td colspan="5" class="py-6 text-center text-slate-500">Add at least one line.</td>
                    </tr>
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="3" class="text-right font-semibold text-slate-700">Total</td>
                        <td class="text-right text-base font-semibold text-slate-900">{{ formatCLP(total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving…' : 'Create draft' }}</button>
        </div>
    </form>
</template>
