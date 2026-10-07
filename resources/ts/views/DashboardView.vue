<script setup lang="ts">
import { onMounted } from 'vue';
import { dashboardApi } from '@/api/catalog';
import AlertMessage from '@/components/AlertMessage.vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useAsync } from '@/composables/useAsync';
import { formatCLP, formatNumber } from '@/composables/useFormat';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const { data: dashboard, loading, error, run: load } = useAsync(dashboardApi.get);

onMounted(load);

function stockPercent(quantity: number, min: number): number {
    return Math.min(100, Math.round((quantity / Math.max(min, 1)) * 100));
}
</script>

<template>
    <PageHeader :title="`Hi, ${auth.user?.name.split(' ')[0]} 👋`" subtitle="Here is what needs your attention across all branches today.">
        <RouterLink to="/purchase-orders/new" class="btn btn-primary">
            <AppIcon name="plus" class="size-4" /> New purchase order
        </RouterLink>
    </PageHeader>

    <AlertMessage :message="error" />

    <div v-if="loading && !dashboard" class="text-sm text-slate-500">Loading…</div>

    <template v-if="dashboard">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard label="Branches" :value="dashboard.stats.branches" icon="pin" />
            <StatCard label="Products" :value="dashboard.stats.products" icon="cube" tone="emerald" />
            <StatCard label="Open purchase orders" :value="dashboard.stats.open_purchase_orders" icon="document" tone="amber" />
            <StatCard label="Low-stock alerts" :value="dashboard.stats.low_stock_items" icon="warning" tone="rose" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
            <section class="card overflow-hidden lg:col-span-3">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="flex items-center gap-2 font-semibold text-slate-900">
                        <AppIcon name="warning" class="size-5 text-rose-500" /> Low stock
                    </h2>
                    <span class="text-xs text-slate-500">Below the product minimum</span>
                </header>
                <table class="table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th>Branch</th>
                            <th>Product</th>
                            <th>On hand / min</th>
                            <th class="text-right!"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in dashboard.low_stock" :key="`${item.branch_id}-${item.product_id}`">
                            <td class="font-medium text-slate-900">{{ item.branch_name }}</td>
                            <td>
                                {{ item.product_name }}
                                <span class="ml-1 text-xs text-slate-400">{{ item.sku }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-20 rounded-full bg-slate-100">
                                        <div class="h-1.5 rounded-full bg-rose-500" :style="{ width: `${stockPercent(item.quantity, item.min_stock)}%` }" />
                                    </div>
                                    <span class="text-xs">{{ formatNumber(item.quantity) }} / {{ formatNumber(item.min_stock) }} {{ item.unit }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <RouterLink
                                    :to="{ name: 'purchase-orders.create', query: { branch: item.branch_id, product: item.product_id, quantity: item.shortage } }"
                                    class="text-sm font-medium text-indigo-600 hover:underline"
                                >
                                    Reorder
                                </RouterLink>
                            </td>
                        </tr>
                        <tr v-if="!dashboard.low_stock.length">
                            <td colspan="4" class="py-8 text-center text-slate-500">All branches are above their minimum stock. 🎉</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="card lg:col-span-2">
                <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Recent purchase orders</h2>
                    <RouterLink to="/purchase-orders" class="text-sm text-indigo-600 hover:underline">View all</RouterLink>
                </header>
                <ul class="divide-y divide-slate-100">
                    <li v-for="order in dashboard.recent_purchase_orders" :key="order.id">
                        <RouterLink :to="`/purchase-orders/${order.id}`" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ order.reference }} · {{ order.supplier }}</p>
                                <p class="text-xs text-slate-500">{{ order.branch?.name }} · {{ formatCLP(order.total) }}</p>
                            </div>
                            <StatusBadge :status="order.status" />
                        </RouterLink>
                    </li>
                </ul>
            </section>
        </div>
    </template>
</template>
