<script setup lang="ts">
import { computed } from 'vue';
import type { PurchaseOrderStatus } from '@/types';
import AppIcon from './AppIcon.vue';

const props = defineProps<{ status: PurchaseOrderStatus }>();

const steps: { key: PurchaseOrderStatus; label: string }[] = [
    { key: 'draft', label: 'Draft' },
    { key: 'approved', label: 'Approved' },
    { key: 'received', label: 'Received' },
];

const currentIndex = computed(() => steps.findIndex((step) => step.key === props.status));
</script>

<template>
    <ol class="flex items-center gap-2">
        <li v-for="(step, index) in steps" :key="step.key" class="flex items-center gap-2">
            <span
                class="flex size-7 items-center justify-center rounded-full text-xs font-semibold"
                :class="index <= currentIndex ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500'"
            >
                <AppIcon v-if="index < currentIndex" name="check" class="size-4" />
                <template v-else>{{ index + 1 }}</template>
            </span>
            <span class="text-sm" :class="index <= currentIndex ? 'font-medium text-slate-900' : 'text-slate-400'">{{ step.label }}</span>
            <span v-if="index < steps.length - 1" class="mx-1 h-px w-8 bg-slate-300" />
        </li>
    </ol>
</template>
