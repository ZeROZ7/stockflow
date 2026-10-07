<script setup lang="ts">
import { ref, watch } from 'vue';
import { errorMessage } from '@/api/http';
import { purchaseOrdersApi } from '@/api/purchaseOrders';
import AlertMessage from '@/components/AlertMessage.vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import StatusStepper from '@/components/StatusStepper.vue';
import { useAsync } from '@/composables/useAsync';
import { formatCLP, formatDate, formatNumber } from '@/composables/useFormat';

const props = defineProps<{ id: number }>();

const { data: order, loading, error, run: load } = useAsync(purchaseOrdersApi.find);
const actionError = ref<string | null>(null);
const busy = ref(false);

watch(() => props.id, (id) => load(id), { immediate: true });

async function transition(action: 'approve' | 'receive'): Promise<void> {
    const question = action === 'approve' ? 'Approve this purchase order?' : 'Confirm the goods arrived? Stock will be increased.';
    if (!confirm(question)) return;

    busy.value = true;
    actionError.value = null;

    try {
        order.value = await purchaseOrdersApi[action](props.id);
    } catch (e) {
        actionError.value = errorMessage(e);
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <AlertMessage :message="error" />
    <div v-if="loading && !order" class="text-sm text-slate-500">Loading…</div>

    <template v-if="order">
        <PageHeader :title="order.reference" :subtitle="`${order.supplier} → ${order.branch?.name}`">
            <RouterLink to="/purchase-orders" class="btn btn-secondary">
                <AppIcon name="arrowLeft" class="size-4" /> Back
            </RouterLink>
            <button v-if="order.can.approve" type="button" class="btn btn-primary" :disabled="busy" @click="transition('approve')">
                <AppIcon name="check" class="size-4" /> Approve
            </button>
            <button v-if="order.can.receive" type="button" class="btn btn-success" :disabled="busy" @click="transition('receive')">
                <AppIcon name="truck" class="size-4" /> Mark as received
            </button>
        </PageHeader>

        <AlertMessage :message="actionError" />

        <section class="card mb-6 flex flex-wrap items-center justify-between gap-4 p-5">
            <StatusStepper :status="order.status" />
            <StatusBadge :status="order.status" />
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="card overflow-hidden lg:col-span-2">
                <table class="table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th>Product</th>
                            <th class="text-right!">Quantity</th>
                            <th class="text-right!">Unit cost</th>
                            <th class="text-right!">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="line in order.lines" :key="line.id">
                            <td>
                                <span class="font-medium text-slate-900">{{ line.product?.name }}</span>
                                <span class="ml-1 text-xs text-slate-400">{{ line.product?.sku }}</span>
                            </td>
                            <td class="text-right">{{ formatNumber(line.quantity) }} {{ line.product?.unit }}</td>
                            <td class="text-right">{{ formatCLP(line.unit_cost) }}</td>
                            <td class="text-right font-medium text-slate-900">{{ formatCLP(line.subtotal) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="3" class="text-right font-semibold text-slate-700">Total</td>
                            <td class="text-right text-base font-semibold text-slate-900">{{ formatCLP(order.total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <aside class="card p-5">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Branch</dt>
                        <dd class="font-medium text-slate-900">{{ order.branch?.name }} ({{ order.branch?.code }})</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created</dt>
                        <dd class="font-medium text-slate-900">{{ formatDate(order.created_at) }} · {{ order.created_by }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Approved</dt>
                        <dd class="font-medium text-slate-900">
                            {{ formatDate(order.approved_at) }}<template v-if="order.approved_by"> · {{ order.approved_by }}</template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Received</dt>
                        <dd class="font-medium text-slate-900">
                            {{ formatDate(order.received_at) }}<template v-if="order.received_by"> · {{ order.received_by }}</template>
                        </dd>
                    </div>
                    <div v-if="order.notes">
                        <dt class="text-slate-500">Notes</dt>
                        <dd class="text-slate-700">{{ order.notes }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </template>
</template>
