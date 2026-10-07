<script setup lang="ts">
import { ref, watch } from 'vue';
import { purchaseOrdersApi } from '@/api/purchaseOrders';
import AlertMessage from '@/components/AlertMessage.vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useAsync } from '@/composables/useAsync';
import { formatCLP, formatDate } from '@/composables/useFormat';
import type { PurchaseOrderStatus } from '@/types';

const tabs: { label: string; value: PurchaseOrderStatus | undefined }[] = [
    { label: 'All', value: undefined },
    { label: 'Draft', value: 'draft' },
    { label: 'Approved', value: 'approved' },
    { label: 'Received', value: 'received' },
];

const status = ref<PurchaseOrderStatus | undefined>(undefined);
const page = ref(1);
const { data: result, loading, error, run: load } = useAsync(purchaseOrdersApi.list);

watch(status, () => (page.value = 1));
watch([status, page], () => load({ status: status.value, page: page.value }), { immediate: true });
</script>

<template>
    <PageHeader title="Purchase orders" subtitle="Draft → approved by a manager → received at the branch (stock goes up).">
        <RouterLink to="/purchase-orders/new" class="btn btn-primary">
            <AppIcon name="plus" class="size-4" /> New purchase order
        </RouterLink>
    </PageHeader>

    <AlertMessage :message="error" />

    <div class="mb-4 inline-flex rounded-lg bg-white p-1 shadow-sm ring-1 ring-slate-200">
        <button
            v-for="tab in tabs"
            :key="tab.label"
            type="button"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition"
            :class="status === tab.value ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900'"
            @click="status = tab.value"
        >
            {{ tab.label }}
        </button>
    </div>

    <div class="card overflow-hidden">
        <table class="table">
            <thead class="bg-slate-50">
                <tr>
                    <th>Reference</th>
                    <th>Branch</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th class="text-right!">Total</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" :class="{ 'opacity-50': loading }">
                <tr v-for="order in result?.data ?? []" :key="order.id" class="hover:bg-slate-50">
                    <td>
                        <RouterLink :to="`/purchase-orders/${order.id}`" class="font-medium text-indigo-600 hover:underline">{{ order.reference }}</RouterLink>
                    </td>
                    <td>{{ order.branch?.name }}</td>
                    <td>{{ order.supplier }}</td>
                    <td><StatusBadge :status="order.status" /></td>
                    <td class="text-right font-medium text-slate-900">{{ formatCLP(order.total) }}</td>
                    <td class="text-slate-500">{{ formatDate(order.created_at) }}</td>
                </tr>
                <tr v-if="!loading && !result?.data.length">
                    <td colspan="6" class="py-8 text-center text-slate-500">No purchase orders yet.</td>
                </tr>
            </tbody>
        </table>

        <footer v-if="result && result.meta.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm">
            <span class="text-slate-500">Page {{ result.meta.current_page }} of {{ result.meta.last_page }}</span>
            <div class="flex gap-2">
                <button type="button" class="btn btn-secondary" :disabled="page <= 1" @click="page--">Previous</button>
                <button type="button" class="btn btn-secondary" :disabled="page >= result.meta.last_page" @click="page++">Next</button>
            </div>
        </footer>
    </div>
</template>
