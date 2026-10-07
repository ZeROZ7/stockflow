<script setup lang="ts">
import { computed } from 'vue';
import type { PurchaseOrderStatus } from '@/types';

const props = defineProps<{ status: PurchaseOrderStatus }>();

const styles: Record<PurchaseOrderStatus, string> = {
    draft: 'bg-slate-100 text-slate-700 ring-slate-300',
    approved: 'bg-amber-50 text-amber-800 ring-amber-300',
    received: 'bg-emerald-50 text-emerald-700 ring-emerald-300',
};

const label = computed(() => props.status.charAt(0).toUpperCase() + props.status.slice(1));
</script>

<template>
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset" :class="styles[status]">
        {{ label }}
    </span>
</template>
